<?php

namespace App\Http\Controllers;

use App\Models\SmsGateway;
use App\Models\SmsMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SmsGatewayController extends Controller
{
    /* =================================================================
     |  DASHBOARD (the only blade page — everything else is JSON/AJAX)
     ================================================================= */

    public function index()
    {
        $gateways = SmsGateway::orderByDesc('active')
                              ->orderBy('name')
                              ->get();

        $activeGateway = SmsGateway::getActive();

        $messages = SmsMessage::with(['gateway', 'sender'])
                              ->latest()
                              ->limit(100)
                              ->get();

        $stats = [
            'total_gateways'  => $gateways->count(),
            'active_gateway'  => $activeGateway?->name ?? 'None',
            'total_messages'  => SmsMessage::count(),
            'sent_today'      => SmsMessage::whereDate('created_at', today())->count(),
            'failed_today'    => SmsMessage::whereDate('created_at', today())
                                          ->where('status', 'failed')
                                          ->count(),
        ];

        return view('backend.setting.sms_dashboard', compact(
            'gateways', 'activeGateway', 'messages', 'stats'
        ));
    }

    /* =================================================================
     |  JSON endpoints used by the modals
     ================================================================= */

    /**
     * POST /sms-gateways  — create (called from the Create modal)
     */
    public function store(Request $request)
    {
        $validated = $this->validateGateway($request);

        DB::transaction(function () use ($validated) {
            if ($validated['active']) {
                SmsGateway::where('active', true)->update(['active' => false]);
            }
            SmsGateway::create($validated);
        });

        return response()->json([
            'success' => true,
            'message' => 'SMS gateway created successfully.',
        ]);
    }

    /**
     * GET /sms-gateways/{sms_gateway}  — data for the View modal
     */
    public function show(SmsGateway $smsGateway)
    {
        $messages = $smsGateway->messages()
                               ->with('sender')
                               ->latest()
                               ->limit(20)
                               ->get()
                               ->map(fn ($m) => [
                                   'created_at' => $m->created_at?->format('Y-m-d H:i'),
                                   'recipients' => Str::limit($m->recipients, 30),
                                   'message'    => Str::limit($m->message, 50),
                                   'sender'     => $m->sender?->name ?? 'System',
                                   'status'     => $m->status,
                               ]);

        return response()->json([
            'gateway'  => $smsGateway,
            'messages' => $messages,
        ]);
    }

    /**
     * GET /sms-gateways/{sms_gateway}/edit  — data for the Edit modal
     */
    public function edit(SmsGateway $smsGateway)
    {
        return response()->json($smsGateway);
    }

    /**
     * PUT/PATCH /sms-gateways/{sms_gateway}  — update (called from the Edit modal)
     */
    public function update(Request $request, SmsGateway $smsGateway)
    {
        $validated = $this->validateGateway($request, $smsGateway);

        DB::transaction(function () use ($validated, $smsGateway) {
            if ($validated['active'] && ! $smsGateway->active) {
                SmsGateway::where('active', true)
                          ->where('id', '!=', $smsGateway->id)
                          ->update(['active' => false]);
            }
            $smsGateway->update($validated);
        });

        return response()->json([
            'success' => true,
            'message' => 'SMS gateway updated successfully.',
        ]);
    }

    /**
     * DELETE /sms-gateways/{sms_gateway}
     */
    public function destroy(SmsGateway $smsGateway)
    {
        if ($smsGateway->active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the active gateway. Activate another one first.',
            ], 422);
        }

        // Preserve SMS history: detach the FK before deleting the gateway.
        $smsGateway->messages()->update(['sms_gateway_id' => null]);
        $smsGateway->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gateway deleted.',
        ]);
    }

    /* =================================================================
     |  ACTIVATE  (single-active enforcement)
     ================================================================= */

    /**
     * POST /sms-gateways/{sms_gateway}/activate
     *
     * Makes the chosen gateway the only active one. Any previously-active
     * gateway is deactivated atomically inside a DB transaction.
     */
    public function activate(SmsGateway $smsGateway)
    {
        $smsGateway->markAsActive();

        return response()->json([
            'success' => true,
            'message' => "`{$smsGateway->name}` is now the active SMS gateway.",
        ]);
    }

    /* =================================================================
     |  Internal helpers
     ================================================================= */

    /**
     * Shared validation — matches your SmsGateway fillable:
     *   name, key, sender, active
     */
    protected function validateGateway(Request $request, ?SmsGateway $ignore = null): array
    {
        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'key'    => 'required|string|max:255',
            'sender' => 'nullable|string|max:255',
            'active' => 'sometimes|boolean',
        ]);

        $data['active'] = (bool) ($data['active'] ?? false);

        return $data;
    }
}
