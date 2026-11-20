<?php

namespace App\Http\Controllers;

use App\Models\VariantType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VariantTypeController extends Controller
{
    public function index()
    {
        $variantTypes = VariantType::ordered()->get();


        return view('backend.variant_type.index', compact('variantTypes'));
    }

    public function create()
    {
        return view('backend.variant_type.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'order'  => ['required', 'integer', 'min:0', 'unique:variant_types,order'],
            'active' => ['sometimes', 'boolean'],
        ]);

        $validated['active'] = $request->boolean('active', true);

        VariantType::create($validated);

        return redirect()
            ->route('variant-types.index')
            ->with('success', 'Variant type created.');
    }

    public function show(VariantType $variantType)
    {
        return view('backend.variant_type.show', compact('variantType'));
    }

    public function edit(VariantType $variantType)
    {
        return view('backend.variant_type.edit', compact('variantType'));
    }

    public function update(Request $request, VariantType $variantType)
    {
        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'order'  => [
                'required', 'integer', 'min:0',
                Rule::unique('variant_types', 'order')->ignore($variantType->id),
            ],
            'active' => ['sometimes', 'boolean'],
        ]);

        $validated['active'] = $request->boolean('active');

        $variantType->update($validated);

        return redirect()
            ->route('variant-types.index')
            ->with('success', 'Variant type updated.');
    }

    /** Custom action — must be registered manually (see routes below). */
    public function changeOrder(Request $request, VariantType $variantType)
    {
        $validated = $request->validate([
            'order' => [
                'required', 'integer', 'min:0',
                Rule::unique('variant_types', 'order')->ignore($variantType->id),
            ],
        ]);

        $variantType->update(['order' => $validated['order']]);

        return redirect()
            ->route('variant-types.index')
            ->with('success', 'Order updated.');
    }

    public function destroy(VariantType $variantType)
    {
        // Now works thanks to the hasManyThrough relation.
        if ($variantType->productVariants()->exists()) {
            return redirect()
                ->back()
                ->withErrors([
                    'variantType' => 'Cannot delete a variant type that is being used by a product variant.',
                ]);
        }

        $variantType->delete();

        return redirect()
            ->route('variant-types.index')
            ->with('success', 'Variant type deleted.');
    }
}
