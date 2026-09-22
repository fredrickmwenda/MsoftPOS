<?php

namespace App\Contracts;

use App\DTO\AssistantMessageData;
use App\DTO\AssistantContextData;
use App\DTO\AssistantResponseData;

interface AssistantSkill
{
    public function key(): string;
    public function name(): string;
    public function description(): string;
    public function examples(): array;
    public function canHandle(AssistantMessageData $message): bool;
    public function handle(AssistantMessageData $message, AssistantContextData $context): AssistantResponseData;
}
