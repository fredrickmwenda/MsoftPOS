<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPolicyController extends Controller
{
    public function index()
    {
        $policies = Policy::orderBy('sort_order')
            ->orderBy('title')
            ->paginate(15);

        return view('backend.policies.index', compact('policies'));
    }

    public function create()
    {
        return view('backend.policies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:policies,slug',
            'body'             => 'required|string',
            'excerpt'          => 'nullable|string|max:1000',
            'meta_description' => 'nullable|string|max:255',
            'sort_order'       => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        } else {
            $validated['slug'] = Str::slug($validated['slug']);
        }

        Policy::create($validated);

        return redirect()
            ->route('policies.index')
            ->with('success', 'Policy created successfully.');
    }

    public function edit(Policy $policy)
    {
        return view('backend.policies.edit', compact('policy'));
    }

    public function update(Request $request, Policy $policy)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:policies,slug,' . $policy->id,
            'body'             => 'required|string',
            'excerpt'          => 'nullable|string|max:1000',
            'meta_description' => 'nullable|string|max:255',
            'sort_order'       => 'nullable|integer|min:0',
        ]);

        $validated['is_active']   = $request->boolean('is_active');
        $validated['sort_order']  = $validated['sort_order'] ?? $policy->sort_order;

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        } else {
            $validated['slug'] = Str::slug($validated['slug']);
        }

        $policy->update($validated);

        return redirect()
            ->route('backend.policies.index')
            ->with('success', 'Policy updated successfully.');
    }

    public function destroy(Policy $policy)
    {
        $policy->delete();

        return redirect()
            ->route('policies.index')
            ->with('success', 'Policy deleted.');
    }

    public function toggleActive(Policy $policy)
    {
        $policy->update(['is_active' => ! $policy->is_active]);

        return back()->with('success', 'Policy status updated.');
    }
}