<?php

namespace App\Http\Controllers;

use App\Models\AIProviderSetting;
use App\Models\GeneralSetting;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AIAssistantController extends Controller
{
    /**
     * Display AI Assistant main chat page.
     */
    public function index()
    {
        $theme = env('THEME', 'default');
        if (Schema::hasTable('general_settings')) {
            $theme = GeneralSetting::latest()->value('theme');
        }
        $languages = Language::orderBy('code')->get();
        $activeProvider = \App\Services\AIProviderResolver::active();
        $providerLabel  = \App\Services\AIProviderResolver::label();

        return view('backend.ai.index', compact('theme', 'languages', 'activeProvider', 'providerLabel'));

    }

    /**
     * Display the AI Provider Settings management page.
     */
    public function providers()
    {
        $theme = env('THEME', 'default');
        if (Schema::hasTable('general_settings')) {
            $theme = GeneralSetting::latest()->value('theme');
        }
        $languages = Language::orderBy('code')->get();

        $providers = AIProviderSetting::orderByDesc('id')->get();

        // Mask API keys so we never leak the full secret to the browser.
        // The model hides api_key from array casting, but direct property
        // access still decrypts it. We only expose the last 4 chars.
        $providers->each(function ($p) {
            $key = $p->api_key;
            $p->masked_api_key = $key ? str_repeat('•', 12) . substr($key, -4) : '';
        });

        return view('backend.ai.providers', compact('theme', 'languages', 'providers'));
    }

    /**
     * Store a new provider setting.
     */
    public function providerStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider'   => 'required|string|max:50',
            'api_key'    => 'required|string|max:512',
            'base_url'   => 'nullable|url|max:255',
            'model'      => 'nullable|string|max:100',
            'settings'   => 'nullable|string',
            'is_enabled' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $settings = $this->decodeSettings($request->input('settings'));
        if ($settings === false) {
            return response()->json(['errors' => ['settings' => ['Invalid JSON in settings field.']]], 422);
        }

        // Only one provider can be active at a time.
        if ($request->boolean('is_enabled')) {
            AIProviderSetting::where('is_enabled', true)->update(['is_enabled' => false]);
        }

        $provider = AIProviderSetting::create([
            'provider'   => $request->input('provider'),
            'api_key'    => $request->input('api_key'),
            'base_url'   => $request->input('base_url'),
            'model'      => $request->input('model'),
            'settings'   => $settings,
            'is_enabled' => $request->boolean('is_enabled'),
        ]);

        Log::info('AI Provider created', ['id' => $provider->id, 'provider' => $provider->provider]);

        return response()->json([
            'success'  => true,
            'message'  => 'Provider created successfully.',
            'provider' => $provider,
        ]);
    }

    /**
     * Update an existing provider.
     */
    public function providerUpdate(Request $request, $id)
    {
        $provider = AIProviderSetting::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'provider'   => 'sometimes|string|max:50',
            'api_key'    => 'sometimes|string|max:512',
            'base_url'   => 'nullable|url|max:255',
            'model'      => 'nullable|string|max:100',
            'settings'   => 'nullable|string',
            'is_enabled' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if (array_key_exists('settings', $data)) {
            $decoded = $this->decodeSettings($data['settings']);
            if ($decoded === false) {
                return response()->json(['errors' => ['settings' => ['Invalid JSON in settings field.']]], 422);
            }
            $data['settings'] = $decoded;
        }

        // Only one provider can be active at a time.
        if (!empty($data['is_enabled'])) {
            AIProviderSetting::where('id', '!=', $id)
                ->where('is_enabled', true)
                ->update(['is_enabled' => false]);
        }

        $provider->update($data);

        Log::info('AI Provider updated', ['id' => $provider->id]);

        return response()->json([
            'success'  => true,
            'message'  => 'Provider updated successfully.',
            'provider' => $provider,
        ]);
    }

    /**
     * Delete a provider.
     */
    public function providerDestroy($id)
    {
        $provider = AIProviderSetting::findOrFail($id);
        $provider->delete();

        Log::info('AI Provider deleted', ['id' => $id]);

        return response()->json([
            'success' => true,
            'message' => 'Provider deleted successfully.',
        ]);
    }

    /**
     * Toggle the enabled status of a provider.
     */
    public function providerToggle($id)
    {
        $provider = AIProviderSetting::findOrFail($id);

        // Only one active at a time.
        if (!$provider->is_enabled) {
            AIProviderSetting::where('id', '!=', $id)
                ->where('is_enabled', true)
                ->update(['is_enabled' => false]);
        }

        $provider->is_enabled = !$provider->is_enabled;
        $provider->save();

        return response()->json([
            'success'    => true,
            'is_enabled' => $provider->is_enabled,
            'message'    => $provider->is_enabled ? 'Provider enabled.' : 'Provider disabled.',
        ]);
    }

    /**
     * Decode JSON settings, returning null for empty strings.
     * Returns false on JSON parse error.
     */
    private function decodeSettings(?string $json)
    {
        if ($json === null || trim($json) === '') {
            return null;
        }
        $decoded = json_decode($json, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : false;
    }
}