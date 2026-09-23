<?php

namespace App\Services;

use App\Models\AIProviderSetting;

class AIProviderResolver
{
    /**
     * Return the currently-enabled AI provider (if any).
     * Only one row should be enabled at a time — the controller enforces this.
     */
    public static function active(): ?AIProviderSetting
    {
        return AIProviderSetting::where('is_enabled', true)->first();
    }

    public static function hasExternal(): bool
    {
        return self::active() !== null;
    }

    /**
     * Convenience: returns a human label like "OpenAI · gpt-4o"
     * or "Free Structured Assistant" when no provider is enabled.
     */
    public static function label(): string
    {
        $p = self::active();
        if (! $p) {
            return 'Free Structured Assistant';
        }
        return ucfirst($p->provider) . ($p->model ? ' · ' . $p->model : '');
    }
}