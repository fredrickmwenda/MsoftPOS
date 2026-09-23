<?php

namespace App\Http\Controllers;

use App\Http\Requests\StructuredPromptRequest;
use App\Models\AIConversation;
use App\Services\AssistantExecutionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AIConversationController extends Controller
{
    /**
     * Verify the AI Assistant persistence tables exist on the active connection.
     */
    private function missingSchemaResponse()
    {
        $requiredTables = [
            'ai_conversations',
            'ai_messages',
            'ai_skill_runs',
        ];

        try {
            $missingTables = array_values(array_filter(
                $requiredTables,
                fn ($table) => ! Schema::hasTable($table)
            ));
        } catch (Throwable $e) {
            Log::error('AI Assistant schema check failed', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'AI Assistant database setup could not be verified. Please run the AI Assistant migrations.',
                'code'  => 'ai_assistant_schema_check_failed',
            ], 503);
        }

        if (empty($missingTables)) {
            return null;
        }

        Log::warning('AI Assistant tables are missing on active database connection', [
            'user_id'        => Auth::id(),
            'missing_tables' => $missingTables,
        ]);

        return response()->json([
            'error'          => 'AI Assistant database tables are missing. Please run migrations.',
            'code'           => 'ai_assistant_migrations_missing',
            'missing_tables' => $missingTables,
        ], 503);
    }

    /**
     * Retrieve a paginated list of conversations for the authenticated user.
     */
    public function index(): JsonResponse
    {
        if ($schemaError = $this->missingSchemaResponse()) {
            return $schemaError;
        }

        try {
            $conversations = AIConversation::forUser(Auth::id())
                ->select('id', 'title', 'updated_at')
                ->orderByDesc('updated_at')
                ->paginate(20);

            return response()->json($conversations);
        } catch (Throwable $e) {
            Log::error('AI Assistant conversation index failed', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'An error occurred loading AI Assistant history. Please try again.',
            ], 500);
        }
    }

    /**
     * Create a new conversation and execute the first prompt.
     */
    public function store(
        StructuredPromptRequest $request,
        AssistantExecutionService $executionService
    ): JsonResponse {
        try {
            if ($schemaError = $this->missingSchemaResponse()) {
                return $schemaError;
            }

            $user = Auth::user();
            $prompt = $request->validated('prompt');

            $result = $executionService->executeAndPersist($prompt, $user, null);

            return response()->json([
                'conversation' => $result['conversation'],
                'response'     => $result['response']->toArray(),
            ]);
        } catch (Throwable $e) {
            Log::error('AI Assistant conversation store failed', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'An error occurred processing your request. Please try again.',
            ], 500);
        }
    }

    /**
     * Load a specific conversation and its paginated messages.
     */
    public function show($id): JsonResponse
    {
        if ($schemaError = $this->missingSchemaResponse()) {
            return $schemaError;
        }

        $conversation = AIConversation::forUser(Auth::id())->findOrFail($id);

        // Load messages with deterministic ordering, paginated.
        // We order by latest first to bound the page, then the frontend should reverse them for display.
        $messages = $conversation->messages()
                                 ->orderBy('created_at', 'desc')
                                 ->orderBy('id', 'desc')
                                 ->paginate(50);

        return response()->json([
            'conversation' => $conversation,
            'messages'     => $messages,
        ]);
    }

    /**
     * Submit a new prompt to an existing conversation.
     */
    public function appendPrompt(
        $id,
        StructuredPromptRequest $request,
        AssistantExecutionService $executionService
    ): JsonResponse {
        try {
            if ($schemaError = $this->missingSchemaResponse()) {
                return $schemaError;
            }

            $user = Auth::user();
            $prompt = $request->validated('prompt');

            $conversation = AIConversation::forUser($user->id)->findOrFail($id);

            $result = $executionService->executeAndPersist($prompt, $user, $conversation);

            return response()->json([
                'conversation' => $result['conversation'],
                'response'     => $result['response']->toArray(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Model not found: ' . $e->getMessage()], 404);
        } catch (Throwable $e) {
            Log::error('AI Assistant conversation append failed', [
                'user_id'         => Auth::id(),
                'conversation_id' => $id,
                'error'           => $e->getMessage(),
                'trace'           => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'An error occurred processing your request. Please try again.',
            ], 500);
        }
    }

    /**
     * Safely delete a conversation and its messages.
     */
    public function destroy($id): JsonResponse
    {
        if ($schemaError = $this->missingSchemaResponse()) {
            return $schemaError;
        }

        $conversation = AIConversation::forUser(Auth::id())->findOrFail($id);

        DB::transaction(function () use ($conversation) {
            $conversation->messages()->delete();
            $conversation->delete();
        });

        return response()->json(['success' => true]);
    }
}