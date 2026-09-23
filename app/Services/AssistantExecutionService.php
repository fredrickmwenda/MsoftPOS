<?php

namespace App\Services;

use App\DTO\AssistantContextData;
use App\DTO\AssistantMessageData;
use App\DTO\AssistantResponseData;
use App\Models\AIConversation;
use App\Models\AIMessage;
use App\Models\AIProviderSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AssistantExecutionService
{
    public function __construct(
        private AssistantOrchestrator $orchestrator
    ) {}

    /**
     * Resolves the allowed warehouse IDs for the authenticated user.
     *
     * Rules:
     * - Admin / role_id <= 2: no warehouse restriction (null = all warehouses)
     * - Staff with staff_access == 'warehouse': restricted to their assigned warehouse
     * - Staff with staff_access == 'own': restricted to their rows ('own')
     */
    private function resolveWarehouseIds(User $user): array|string|null
    {
        if ($user->role_id <= 2) {
            return null;
        }

        $generalSetting = Cache::get('general_setting');
        $staffAccess = optional($generalSetting)->staff_access ?? 'all';

        if ($staffAccess === 'warehouse') {
            return $user->warehouse_id ? [(int) $user->warehouse_id] : [];
        }

        if ($staffAccess === 'own') {
            return 'own';
        }

        return null;
    }

    /**
     * Resolve the currently-enabled AI provider, if any.
     *
     * This is the single point where the chat UI is linked to the
     * ai_provider_settings table. Returns null when no provider is
     * enabled (free structured mode).
     */
    private function resolveActiveProvider(): ?AIProviderSetting
    {
        return AIProviderSetting::where('is_enabled', true)->first();
    }

    /**
     * Executes the prompt without persisting any conversation.
     * Used by the generic /prompt endpoint — ALWAYS structured mode.
     */
    public function execute(string $prompt, User $user): AssistantResponseData
    {
        $warehouseIdsOrOwn = $this->resolveWarehouseIds($user);

        $businessContext = [];
        if (is_array($warehouseIdsOrOwn)) {
            $businessContext['warehouse_ids'] = $warehouseIdsOrOwn;
        } elseif ($warehouseIdsOrOwn === 'own') {
            $businessContext['own_user_id'] = $user->id;
        }

        $message = new AssistantMessageData('user', $prompt);
        $context = new AssistantContextData($user->id, $businessContext);

        return $this->orchestrator->executeStructured($message, $context);
    }

    /**
     * Executes the prompt against an external LLM provider using the
     * OpenAI-compatible /chat/completions endpoint.
     *
     * Works for: OpenAI, Anthropic (compat layer), Google (compat), Groq,
     * Mistral, DeepSeek, Ollama (local), and any custom OpenAI-compatible base URL.
     *
     * @return AssistantResponseData
     */
    protected function executeExternal(
        string $prompt,
        User $user,
        ?AIConversation $conversation,
        AIProviderSetting $provider
    ): AssistantResponseData {
        // ── 1. Build chat history from existing conversation messages ──
        // The user's prompt was already persisted before this method is called,
        // so it'll appear at the end of the history naturally.
        $messages = [];

        // Optional: prepend a system message to frame the assistant's role.
        $messages[] = [
            'role'    => 'system',
            'content' => $this->buildSystemPrompt($user, $provider),
        ];

        if ($conversation) {
            // Force a fresh query (don't trust cached relations).
            $existing = AIMessage::where('conversation_id', $conversation->id)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get(['role', 'content']);

            foreach ($existing as $msg) {
                $role = $msg->role === 'user' ? 'user' : 'assistant';
                $messages[] = ['role' => $role, 'content' => $msg->content];
            }
        }

        // ── 2. Build the API payload ──
        $payload = array_merge([
            'model'    => $provider->model,
            'messages' => $messages,
        ], $provider->settings ?? []);

        // ── 3. Determine the endpoint ──
        // Default to OpenAI's public endpoint if the admin left base_url blank.
        $baseUrl  = rtrim($provider->base_url ?: 'https://api.openai.com/v1', '/');
        $endpoint = $baseUrl . '/chat/completions';

        // ── 4. Make the HTTP call ──
        try {
            $httpResponse = Http::withToken($provider->api_key)
                ->timeout(60)
                ->post($endpoint, $payload);

            if ($httpResponse->failed()) {
                throw new \RuntimeException(
                    'LLM API request failed (HTTP ' . $httpResponse->status() . '): '
                    . substr((string) $httpResponse->body(), 0, 500)
                );
            }

            $json = $httpResponse->json();
            $text = $json['choices'][0]['message']['content']
                   ?? $json['choices'][0]['text']
                   ?? '';

            if (trim($text) === '') {
                throw new \RuntimeException('LLM returned an empty response.');
            }

            // ── 5. Wrap into the same DTO the structured backend uses ──
            // We populate only textSummary + responseType; cards/table/etc.
            // are empty because external LLM responses are plain text.
            return $this->buildResponseData(
                textSummary:  $text,
                responseType: 'text',
                metadata: [
                    'engine'        => 'external',
                    'provider'      => $provider->provider,
                    'model'         => $provider->model,
                    'usage'         => $json['usage'] ?? null,
                    'response_id'   => $json['id'] ?? null,
                ],
            );

        } catch (Throwable $e) {
            Log::error('External LLM call failed', [
                'provider'        => $provider->provider,
                'model'           => $provider->model,
                'conversation_id' => $conversation?->id,
                'user_id'         => $user->id,
                'error'           => $e->getMessage(),
            ]);

            // Soft-fail: return an error DTO so the chat UI shows the error
            // gracefully instead of crashing the whole transaction.
            return $this->buildResponseData(
                textSummary:  'The AI provider returned an error: ' . $e->getMessage(),
                responseType: 'error',
                errors:        [$e->getMessage()],
                metadata: [
                    'engine'   => 'external',
                    'provider' => $provider->provider,
                    'error'    => true,
                ],
            );
        }
    }

    /**
     * Build a system prompt that frames the LLM's behaviour.
     * Customize freely — this is just a sensible default.
     */
    protected function buildSystemPrompt(User $user, AIProviderSetting $provider): string
    {
        $name = $user->name ?? 'the user';
        return "You are an AI Assistant integrated into a business management system. "
             . "You are helping {$name} with their business questions. "
             . "Be concise, accurate, and helpful. "
             . "When you don't know something, say so — do not invent data.";
    }

    /**
     * Tiny helper to construct the response DTO without depending on
     * the exact constructor signature of AssistantResponseData.
     *
     * If your DTO uses a different constructor, replace the body of
     * this method with `new AssistantResponseData(...)` using whatever
     * named arguments match.
     */
    protected function buildResponseData(
        string $textSummary,
        string $responseType,
        array  $cards    = [],
        ?array $table    = null,
        array  $warnings = [],
        array  $errors   = [],
        array  $links    = [],
        array  $metadata = []
    ): AssistantResponseData {
        return new AssistantResponseData(
            textSummary:  $textSummary,
            responseType: $responseType,
            cards:        $cards,
            table:        $table,
            warnings:     $warnings,
            errors:       $errors,
            links:        $links,
            metadata:     $metadata,
        );
    }

    /**
     * Executes the prompt and optionally creates/appends to a
     * conversation transactionally.
     *
     * @return array{conversation: AIConversation, response: AssistantResponseData}
     */
    public function executeAndPersist(
        string $prompt,
        User $user,
        ?AIConversation $conversation = null
    ): array {
        return DB::transaction(function () use ($prompt, $user, $conversation) {

            // ── 1. LINK: resolve the active provider ───────────────────
            // This is the single point where the chat UI is linked to the
            // ai_provider_settings table. Null = free structured mode.
            $provider     = $this->resolveActiveProvider();
            $providerName = $provider?->provider ?? 'structured';
            $mode         = $provider ? 'external' : 'structured';

            // ── 2. Create conversation if needed ────────────────────────
            if ($conversation === null) {
                $title = mb_substr($prompt, 0, 100);

                $conversation = AIConversation::create([
                    'user_id'  => $user->id,
                    'provider' => $providerName,
                    'mode'     => $mode,
                    'title'    => $title ?: 'New Conversation',
                ]);
            }

            // ── 3. Persist the user's prompt ───────────────────────────
            AIMessage::create([
                'conversation_id' => $conversation->id,
                'role'           => 'user',
                'content'        => $prompt,
                'response_type'  => 'text',
                'metadata'       => [],
            ]);

            // ── 4. Execute — choose engine based on active provider ────
            if ($provider) {
                $response = $this->executeExternal($prompt, $user, $conversation, $provider);
            } else {
                $response = $this->execute($prompt, $user);
            }

            // ── 5. Persist the assistant's response ───────────────────
            AIMessage::create([
                'conversation_id' => $conversation->id,
                'role'           => 'assistant',
                'content'        => $response->textSummary,
                'response_type'  => $response->responseType,
                'metadata'       => [
                    'cards'    => $response->cards,
                    'table'    => $response->table,
                    'warnings' => $response->warnings,
                    'errors'   => $response->errors,
                    'links'    => $response->links,
                    'metadata' => $response->metadata,
                    'skill'    => $response->metadata['skill'] ?? null,
                    // Persist which engine produced this message so we can
                    // audit later whether it came from local or external.
                    'engine'   => $mode,
                    'provider' => $providerName,
                ],
            ]);

            // Bring the conversation to the top of the history list
            $conversation->touch();

            return [
                'conversation' => $conversation,
                'response'     => $response,
            ];
        });
    }
}