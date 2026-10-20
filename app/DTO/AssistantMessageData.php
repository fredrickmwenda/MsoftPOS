<?php

namespace App\DTO;

use InvalidArgumentException;

final class AssistantMessageData
{
    use ValidatesJsonSafety;

    public function __construct(
        public readonly string $role,
        public readonly string $content,
        public readonly ?string $responseType = null,
        public readonly array $metadata = []
    ) {
        $validRoles = ['system', 'user', 'assistant'];
        if (!in_array($this->role, $validRoles, true)) {
            throw new InvalidArgumentException("Invalid role: {$this->role}. Must be one of: " . implode(', ', $validRoles));
        }

        $validResponseTypes = ['text', 'card', 'table', 'chart', 'error'];
        if ($this->responseType !== null && !in_array($this->responseType, $validResponseTypes, true)) {
            throw new InvalidArgumentException("Invalid response type: {$this->responseType}. Must be one of: " . implode(', ', $validResponseTypes));
        }

        self::ensureJsonPayloadSafe($this->toArray());
    }

    public function toArray(): array
    {
        return [
            'role' => $this->role,
            'content' => $this->content,
            'response_type' => $this->responseType,
            'metadata' => $this->metadata,
        ];
    }
}
