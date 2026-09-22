<?php


namespace App\Contracts;

use App\DTO\AssistantMessageData;
use App\DTO\AssistantContextData;
use App\DTO\AssistantResponseData;



interface AssistantSkillRunRecorder
{
    /**
     * Records a successful skill execution.
     * Persistence failures should follow a deliberate policy (e.g. log and swallow)
     * so that logging failures do not break the actual assistant response.
     */
    public function record(
        AssistantSkill $skill,
        AssistantMessageData $message,
        AssistantContextData $context,
        AssistantResponseData $response,
        int $executionMs
    ): void;
}
