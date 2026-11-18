<?php

namespace App\Http\Controllers;

use App\Models\VariantType;
use Illuminate\Http\Request;

class VariantTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $variantTypes = VariantType::all();
        return view('backend.variant_type.index', compact('variantTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.variant_type.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'order' => 'required|integer',
        ]);
        //check if order is unique
        //
        $variantType = VariantType::where('order', $request->order)->first();
        if ($variantType) {
            return redirect()->back()->withErrors(['order' => 'Order must be unique']);
        }

        VariantType::create($request->all());

        return redirect()->route('variant-types.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $variantType = VariantType::findOrFail($id);
        return view('backend.variant_type.show', compact('variantType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $variantType = VariantType::findOrFail($id);
        return view('backend.variant_type.edit', compact('variantType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $variantType = VariantType::findOrFail($id);
        $variantType->update($request->all());

        return redirect()->route('variant-types.index');
    }


    //change order
    public function changeOrder(Request $request, $id)
    {
        $variantType = VariantType::findOrFail($id);
        $variantType->update(['order' => $request->order]);

        return redirect()->route('variant-types.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // what if it is being used in a product variant?
        // check if it is being used in a product variant
        $variantType = VariantType::findOrFail($id);
        if ($variantType->productVariants()->count() > 0) {
            return redirect()->back()->withErrors(['variantType' => 'Cannot delete variant type that is being used in a product variant']);
        }
        $variantType->delete();

        return redirect()->route('variant-types.index');
    }
}
