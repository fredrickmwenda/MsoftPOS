<?php

namespace App\DTO;

final class AssistantContextData
{
    use ValidatesJsonSafety;

    public function __construct(
        //public ?string $tenantId = null,
        public readonly ?int $userId = null,
        public readonly array $businessContext = [],
        public readonly array $systemContext = []
    ) {
        self::ensureJsonPayloadSafe($this->toArray());
    }

    public function toArray(): array
    {
        return [
            //'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'business_context' => $this->businessContext,
            'system_context' => $this->systemContext,
        ];
    }
}
