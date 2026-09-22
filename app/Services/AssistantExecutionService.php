<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\DTO\AssistantContextData;
use App\DTO\AssistantMessageData;
use App\DTO\AssistantResponseData;
use App\Models\AIConversation;
use App\Models\AIMessage;
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
     * Executes the prompt without persisting any conversation.
     * Used by the generic /prompt endpoint.
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
        $context = new AssistantContextData( $user->id, $businessContext);

        return $this->orchestrator->executeStructured($message, $context);
    }

    /**
     * Executes the prompt and optionally creates/appends to a conversation transactionally.
     *
     * @return array{conversation: AIConversation, response: AssistantResponseData}
     */
public function executeAndPersist(string $prompt, User $user, ?AIConversation $conversation = null): array
{
    return DB::transaction(function () use ($prompt, $user, $conversation) {
        // Create a new conversation only if one wasn't passed in
        if ($conversation === null) {
            // Ensure conversation title is bounded to schema length (usually 255)
            $title = mb_substr($prompt, 0, 100);

            $conversation = AIConversation::create([
                'user_id' => $user->id,
                'provider' => 'structured',
                'mode' => 'structured',
                'title' => $title ?: 'New Conversation',
            ]);
        }

        // Persist User Message
        AIMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $prompt,
            'response_type' => 'text',
            'metadata' => [],
        ]);

        // Execute (this throws if an unexpected error occurs, rolling back the tx)
        $response = $this->execute($prompt, $user);

        // Persist Assistant Message
        AIMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $response->textSummary,
            'response_type' => $response->responseType,
            'metadata' => [
                'cards' => $response->cards,
                'table' => $response->table,
                'warnings' => $response->warnings,
                'errors' => $response->errors,
                'links' => $response->links,
                'metadata' => $response->metadata,
                'skill' => $response->metadata['skill'] ?? null,
            ],
        ]);

        // Update conversation's updated_at to bring it to the top
        $conversation->touch();

        return [
            'conversation' => $conversation,
            'response' => $response,
        ];
    });
}
}
