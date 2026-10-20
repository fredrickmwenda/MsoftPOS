<?php

namespace App\Http\Controllers;

use App\Models\SocialMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SocialMediaController extends Controller
{
    public function index()
    {
        // if (!Auth::user()->hasPermissionTo('social-media')) {
        //     return redirect()->back()->with('not_permitted', 'Access denied');
        // }

        $socialMedias = SocialMedia::orderBy('sort_order')->orderBy('name')->get();
        $icons        = SocialMedia::ICONS;

        return view('backend.social_media.index', compact('socialMedias', 'icons'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $social = SocialMedia::create($data + ['user_id' => Auth::id()]);

        return response()->json([
            'success' => true,
            'message' => 'Social media link added successfully',
            'social'  => $this->payload($social),
        ]);
    }

    public function update(Request $request, $id)
    {
        $social = SocialMedia::findOrFail($id);
        $social->update($this->validated($request));

        return response()->json([
            'success' => true,
            'message' => 'Social media link updated successfully',
            'social'  => $this->payload($social),
        ]);
    }

    public function toggle($id)
    {
        $social = SocialMedia::findOrFail($id);
        $social->update(['is_active' => !$social->is_active]);

        return response()->json([
            'success' => true,
            'message' => $social->is_active ? 'Link activated' : 'Link deactivated',
            'social'  => $this->payload($social),
        ]);
    }

    public function destroy($id)
    {
        SocialMedia::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Social media link deleted successfully',
        ]);
    }

    /* ---------------- helpers ---------------- */

    private function validated(Request $request): array
    {
        // Normalize URL — add scheme if missing so 'url' rule doesn't reject "www..."
        $url = trim($request->input('url', ''));
        if ($url !== '' && !preg_match('~^https?://~i', $url)) {
            $request->merge(['url' => 'https://' . $url]);
        }

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'url'        => 'required|url|max:500',
            'icon'       => ['required', 'string', 'max:100', function ($attr, $value, $fail) {
                // allow preset keys OR a raw inline <svg>
                if (!array_key_exists($value, SocialMedia::ICONS) && !str_starts_with($value, '<svg')) {
                    $fail('Please pick a valid icon.');
                }
            }],
            'sort_order' => 'nullable|integer|min:0',
            // ⚠️ is_active intentionally NOT validated — checkboxes submit "on"
            //    when ticked and nothing when unticked, which fails the `boolean` rule.
        ]);

        // boolean() maps "on" / "1" / "true" / "yes" → true; absent / "0" / "" → false
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    private function payload(SocialMedia $s): array
    {
        return [
            'id'         => $s->id,
            'name'       => $s->name,
            'url'        => $s->url,
            'icon'       => $s->icon,
            'icon_label' => SocialMedia::ICONS[$s->icon]['label'] ?? 'Custom',
            'color'      => $s->brand_color,
            'is_active'  => (bool) $s->is_active,
            'sort_order' => (int) $s->sort_order,
        ];
    }
}
