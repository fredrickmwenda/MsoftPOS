<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StructuredPromptRequest extends FormRequest
{
 /**
     * Maximum allowed prompt length (characters).
     */
    public const MAX_PROMPT_LENGTH = 500;

    /**
     * Determine if the user is authorized to make this request.
     * Permission gate is handled by the route middleware; this always returns true.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules.
     */
    public function rules(): array
    {
        return [
            'prompt' => [
                'required',
                'string',
                'min:1',
                'max:' . self::MAX_PROMPT_LENGTH,
            ],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'prompt.required' => 'A prompt is required.',
            'prompt.string'   => 'The prompt must be a text string.',
            'prompt.min'      => 'The prompt cannot be empty.',
            'prompt.max'      => 'The prompt must not exceed ' . self::MAX_PROMPT_LENGTH . ' characters.',
        ];
    }
}
