<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StorefrontController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->input('q', ''));
        $categorySlug = trim((string) $request->input('category', ''));

        $categories = Category::where('is_active', true)
            ->withCount(['product' => function ($productQuery) {
                $productQuery->where('is_active', true);
            }])
            ->orderBy('name')
            ->get();

        foreach ($categories as $category) {
            $category->slug = Str::slug($category->name);
        }

        $selectedCategory = null;
        if ($categorySlug !== '') {
            $selectedCategory = $categories->first(function ($category) use ($categorySlug) {
                return $category->slug === $categorySlug;
            });
        }

        $featuredQuery = Product::with('category')
            ->where('is_active', true)
            ->where(function ($productQuery) {
                $productQuery->where('is_featured', true)
                    ->orWhere(function ($queryBuilder) {
                        $queryBuilder->whereNull('is_featured')->where('featured', true);
                    });
            });

        if ($selectedCategory) {
            $featuredQuery->where('category_id', $selectedCategory->id);
        }

        if ($query !== '') {
            $featuredQuery->where(function ($productQuery) use ($query) {
                $productQuery->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%")
                    ->orWhere('product_details', 'like', "%{$query}%");
            });
        }

        $featuredProducts = $featuredQuery
            ->orderByDesc('id')
            ->limit(3)
            ->get()
            ->map(function ($product) {
                return $this->attachProductImage($product);
            });

        $featuredIds = $featuredProducts->pluck('id')->all();

        $productsQuery = Product::with('category')
            ->where('is_active', true)
            ->when(!empty($featuredIds), function ($queryBuilder) use ($featuredIds) {
                $queryBuilder->whereNotIn('id', $featuredIds);
            });

        if ($selectedCategory) {
            $productsQuery->where('category_id', $selectedCategory->id);
        }

        if ($query !== '') {
            $productsQuery->where(function ($productQuery) use ($query) {
                $productQuery->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%")
                    ->orWhere('product_details', 'like', "%{$query}%");
            });
        }

        $products = $productsQuery
            ->orderByDesc('id')
            ->paginate(6);

        $products->getCollection()->transform(function ($product) {
            return $this->attachProductImage($product);
        });

        if ($request->ajax()) {
            return view('frontend.shop', compact('products', 'categories', 'featuredProducts', 'selectedCategory', 'query', 'categorySlug'));
        }

        return view('frontend.shop', compact('products', 'categories', 'featuredProducts', 'selectedCategory', 'query', 'categorySlug'));
    }

    protected function attachProductImage($product)
    {
        $image = null;

        if (!empty($product->image)) {
            $images = array_values(array_filter(array_map('trim', explode(',', $product->image))));
            $image = $images[0] ?? null;
        }

        $product->image_url = $image
            ? asset('images/product/' . $image)
            : asset('images/placeholder.png');

        return $product;
    }
}
