<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PaystackPayment extends AbstractPaymentGateway
{
    private string $secretKey;
    private string $publicKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = (string) config('services.paystack.secret');
        $this->publicKey = (string) config('services.paystack.public');
        $this->baseUrl   = (string) config('services.paystack.base_url', 'https://api.paystack.co');

        // NOTE: callback_url is resolved lazily inside callbackUrl()
        // — never here in the constructor, because constructors of services
        // resolved at container build time can fire during console bootstrap
        // (e.g. `php artisan migrate`) when no Request exists.
    }

    /**
     * {@inheritdoc}
     *
     * Validates the incoming request, calls Paystack's
     * /transaction/initialize endpoint, persists a pending Payment row,
     * and returns the authorization URL the client should redirect to.
     */
    public function push(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'       => ['required', 'email'],
            'amount'      => ['required', 'numeric', 'min:1'],
            'currency'    => ['nullable', 'in:NGN,GHS,USD,ZAR,KES'],
            'purchase_id' => ['required', 'exists:purchases,id'],
        ]);

        $purchase  = Purchase::findOrFail($data['purchase_id']);
        $reference = 'psk-' . now()->format('YmdHis') . '-' . $purchase->id;

        $apiResponse = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/transaction/initialize", [
                'email'        => $data['email'],
                'amount'       => (int) ($data['amount'] * 100), // Paystack uses kobo
                'currency'     => $data['currency'] ?? 'NGN',
                'reference'    => $reference,
                'callback_url' => $this->callbackUrl(),
                'metadata'     => [
                    'purchase_id' => $purchase->id,
                    'user_id'     => $request->user()?->id,
                ],
            ])
            ->json() ?? [];

        if (! ($apiResponse['status'] ?? false) || empty($apiResponse['data']['authorization_url'])) {
            Log::warning('Paystack push failed', [
                'reference' => $reference,
                'response'  => $apiResponse,
            ]);

            return response()->json([
                'success' => false,
                'message' => $apiResponse['message'] ?? 'Failed to initialize payment.',
                'reference' => $reference,
            ], 422);
        }

        // Persist a pending Payment row so the webhook can finalize it
        // even if the user never returns to the browser callback URL.
        Payment::create([
            'user_id'           => $request->user()?->id,
            'purchase_id'       => $purchase->id,
            'payment_reference' => $reference,
            'amount'            => $data['amount'],
            'paying_method'     => 'Credit Card',
            'payment_status'    => 0, // 0 = pending, 1 = partial, 2 = paid
            'payment_at'        => now(),
        ]);

        return response()->json([
            'success'           => true,
            'message'           => 'Payment initialized.',
            'authorization_url' => $apiResponse['data']['authorization_url'],
            'reference'         => $reference,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * Used by the frontend to poll transaction status after the user
     * returns from Paystack's authorization page.
     */
    public function queryStatus(Request $request): JsonResponse
    {
        $request->validate([
            'reference' => ['required', 'string'],
        ]);

        $api = $this->verify($request->input('reference'));

        return response()->json([
            'success'   => $api['status'],
            'status'    => $api['gateway_status'],
            'reference' => $request->input('reference'),
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * Handles BOTH the browser redirect-back AND the server-to-server
     * webhook. Distinguishes them by the presence of the
     * `x-paystack-signature` header.
     */
    public function callback(Request $request): JsonResponse
    {
        // ---- Webhook path (server-to-server) ----
        if ($request->hasHeader('x-paystack-signature')) {
            return $this->handleWebhook($request);
        }

        // ---- Browser redirect-back path ----
        $reference = $request->input('reference') ?: $request->input('trxref');

        if (! $reference) {
            return response()->json([
                'success' => false,
                'message' => 'No transaction reference supplied.',
            ], 422);
        }

        $api = $this->verify($reference);

        if (! $api['status'] || $api['gateway_status'] !== 'success') {
            return response()->json([
                'success'  => false,
                'message'  => 'Payment verification failed.',
                'status'   => $api['gateway_status'],
                'reference'=> $reference,
            ], 422);
        }

        $payment = Payment::where('payment_reference', $reference)->first();
        if ($payment) {
            $this->finalizePayment($payment);
        }

        return response()->json([
            'success'   => true,
            'message'   => 'Payment completed.',
            'reference' => $reference,
        ]);
    }

    /**
     * Server-to-server webhook handler.
     * Paystack posts signed JSON events here; signature is HMAC-SHA512
     * of the RAW request body using the secret key.
     */
    private function handleWebhook(Request $request): JsonResponse
    {
        $rawBody   = $request->getContent();
        $signature = $request->header('x-paystack-signature');

        if (! $this->verifyWebhookSignature($rawBody, $signature)) {
            Log::warning('Paystack webhook signature mismatch', [
                'signature' => $signature,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature.',
            ], 401);
        }

        $event = json_decode($rawBody, true);

        if (($event['event'] ?? null) !== 'charge.success') {
            // Acknowledge non-charge events so Paystack doesn't retry them.
            return response()->json([
                'success' => true,
                'message' => 'Ignored non-charge event.',
            ]);
        }

        $reference = $event['data']['reference'] ?? null;
        $payment   = Payment::where('payment_reference', $reference)->first();

        if (! $payment) {
            Log::warning('Paystack webhook for unknown payment', [
                'reference' => $reference,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Unknown payment acknowledged.',
            ]);
        }

        $this->finalizePayment($payment);

        return response()->json([
            'success' => true,
            'message' => 'Finalized.',
        ]);
    }

    /**
     * Verify a Paystack transaction by reference. Returns a normalized array
     * so the rest of the class doesn't have to remember Paystack's response
     * shape (which changes occasionally).
     *
     * @return array{status: bool, gateway_status: string, data: array|null}
     */
    private function verify(string $reference): array
    {
        $body = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transaction/verify/{$reference}")
            ->json() ?? [];

        return [
            'status'         => (bool) ($body['status'] ?? false),
            'gateway_status' => $body['data']['status'] ?? 'unknown',
            'data'           => $body['data'] ?? null,
        ];
    }

    /**
     * Validate the Paystack webhook signature against the raw request body.
     * Paystack signs requests with HMAC-SHA512 of the raw payload using the
     * secret key. You MUST compare against $request->getContent(), not the
     * decoded array, or the hash will be wrong.
     */
    private function verifyWebhookSignature(string $rawPayload, ?string $signature): bool
    {
        if (empty($signature)) {
            return false;
        }

        return hash_equals(
            $signature,
            hash_hmac('sha512', $rawPayload, $this->secretKey)
        );
    }

    /**
     * Resolve the callback URL lazily so we never touch url() during
     * console bootstrap (when no Request exists).
     *
     * Priority:
     *   1. PAYSTACK_CALLBACK_URL env var (recommended for prod)
     *   2. Dynamically-built URL from the current request (HTTP only)
     *
     * @throws RuntimeException when running in console without the env var set
     */
    private function callbackUrl(): string
    {
        $configured = config('services.paystack.callback_url');

        if (! empty($configured)) {
            return $configured;
        }

        if (app()->runningInConsole()) {
            throw new RuntimeException(
                'PAYSTACK_CALLBACK_URL is not set. Define it in .env — '
                . 'Paystack cannot redirect back to a dynamically-built URL '
                . 'during console commands.'
            );
        }

        return url('/payment/paystack/callback');
    }

    /**
     * Public accessor for the public key — the frontend uses this
     * for the inline JS checkout (PaystackPop.setup).
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }
}