<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    // Show the wishlist page
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        
        $wishlistItems = Wishlist::where('customer_id', $customer->id)
            ->with('product')
            ->get();

        return view('backend.customer.wishlist', compact('wishlistItems'));
    }

    // Add item to wishlist
    // Add item to wishlist
    // Add item to wishlist
    public function store(Request $request, $productId)
    {
        $isAjax = $request->ajax() || $request->wantsJson();

        // 1. Check if logged in
        if (!Auth::guard('customer')->check()) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please log in to save items to your wishlist.',
                    'redirect' => route('customer.login')
                ], 401);
            }
            return redirect()->route('customer.login')
                ->with('error', 'Please log in or create an account to save items to your wishlist.');
        }

        $customer = Auth::guard('customer')->user();
        $product = Product::findOrFail($productId);

        // 2. Add to wishlist (avoid duplicates)
        $wishlist = Wishlist::firstOrCreate([
            'customer_id' => $customer->id,
            'product_id' => $product->id
        ]);

        // 3. Get the NEW total count for this customer
        $newCount = Wishlist::where('customer_id', $customer->id)->count();

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => 'Product added to your wishlist!',
                'count' => $newCount // Send the count back to JS
            ]);
        }

        return redirect()->back()->with('success', 'Product added to your wishlist!');
    }

    // Remove item from wishlist
    public function destroy($id)
    {
        $customer = Auth::guard('customer')->user();

        $wishlistItem = Wishlist::where('id', $id)
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        $wishlistItem->delete();

        return redirect()->back()->with('success', 'Item removed from wishlist.');
    }
}