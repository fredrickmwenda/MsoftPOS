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

        // ✅ CHANGED: Only show categories that have at least 1 e-commerce product
        // ✅ CHANGED: Count only active + e-commerce products per category
        $categories = Category::where('is_active', true)
            ->whereHas('product', function ($productQuery) {
                $productQuery->where('is_active', true)
                             ->where('is_ecommerce', true);
            })
            ->withCount(['product' => function ($productQuery) {
                $productQuery->where('is_active', true)
                             ->where('is_ecommerce', true);
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

        // ✅ CHANGED: Added ->where('is_ecommerce', true)
        $featuredQuery = Product::with('category')
            ->where('is_active', true)
            ->where('is_ecommerce', true)
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

        // ✅ CHANGED: Added ->where('is_ecommerce', true)
        $productsQuery = Product::with('category')
            ->where('is_active', true)
            ->where('is_ecommerce', true)
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

    /**
     * View All Products with Filters
     */
    public function viewAllProducts(Request $request)
    {
        $query = trim((string) $request->input('q', ''));
        $categorySlug = trim((string) $request->input('category', ''));
        $minPrice = (float) $request->input('min_price', 0);
        $maxPrice = (float) $request->input('max_price', 0);

        // ✅ CHANGED: Only show categories that have at least 1 e-commerce product
        // ✅ CHANGED: Count only active + e-commerce products per category
        $categories = Category::where('is_active', true)
            ->whereHas('product', function ($productQuery) {
                $productQuery->where('is_active', true)
                             ->where('is_ecommerce', true);
            })
            ->withCount(['product' => function ($productQuery) {
                $productQuery->where('is_active', true)
                             ->where('is_ecommerce', true);
            }])
            ->orderBy('name')
            ->get();

        foreach ($categories as $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        }

        $selectedCategory = null;
        if ($categorySlug !== '') {
            $selectedCategory = $categories->first(function ($category) use ($categorySlug) {
                return $category->slug === $categorySlug;
            });

            if (! $selectedCategory) {
                $selectedCategory = $categories->first(function ($category) use ($categorySlug) {
                    return Str::slug($category->name) === $categorySlug;
                });
            }
        }

        // ✅ CHANGED: Added ->where('is_ecommerce', true)
        $productsQuery = Product::with('category')
            ->where('is_active', true)
            ->where('is_ecommerce', true);

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

        if ($minPrice > 0) {
            $productsQuery->where('price', '>=', $minPrice);
        }
        if ($maxPrice > 0) {
            $productsQuery->where('price', '<=', $maxPrice);
        }

        $products = $productsQuery->orderByDesc('id')->paginate(9)->withQueryString();

        $products->getCollection()->transform(function ($product) {
            return $this->attachProductImage($product);
        });

        return view('frontend.all_products', compact(
            'products',
            'categories',
            'selectedCategory',
            'query',
            'categorySlug',
            'minPrice',
            'maxPrice'
        ));
    }

    /**
     * Show Single Product Details
     */
    public function show($id)
    {
        // ✅ CHANGED: Added ->where('is_ecommerce', true)
        // If a product has is_ecommerce = false, it 404s on the storefront
        $product = Product::with('category')
            ->where('is_active', true)
            ->where('is_ecommerce', true)
            ->findOrFail($id);
        $product = $this->attachProductImage($product);

        // ✅ CHANGED: Added ->where('is_ecommerce', true) to related products
        $relatedProducts = collect();
        if ($product->category_id) {
            $relatedProducts = Product::with('category')
                ->where('is_active', true)
                ->where('is_ecommerce', true)
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->inRandomOrder()
                ->limit(10)
                ->get()
                ->map(function ($p) {
                    return $this->attachProductImage($p);
                });
        }

        return view('frontend.product_detail', compact('product', 'relatedProducts'));
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