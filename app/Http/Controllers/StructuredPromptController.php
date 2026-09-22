<?php

namespace App\Http\Controllers;

use App\Http\Requests\StructuredPromptRequest;
use App\Services\AssistantExecutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class StructuredPromptController extends Controller
{
       /**
     * Execute a structured prompt via the AssistantExecutionService.
     *
     * POST /ai-assistant/prompt
     */
    public function __invoke(
        StructuredPromptRequest $request,
        AssistantExecutionService $executionService
    ): JsonResponse {
        try {
            $user = Auth::user();
            
            // Execute without persisting to a conversation (backward compatible for existing tests)
            $response = $executionService->execute(
                prompt: $request->validated('prompt'),
                user: $user
            );

            return response()->json($response->toArray());

        } catch (Throwable $e) {
            // Log the real error but return a sanitised message — no stack traces exposed
            \Illuminate\Support\Facades\Log::error('AI Assistant structured prompt failed', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'An error occurred processing your request. Please try again.',
            ], 500);
        }
    }
}
