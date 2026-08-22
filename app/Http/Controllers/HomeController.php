<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Store;
use App\Models\Product;
use App\Models\Review;
use App\Models\Wishlist;
use App\Models\FavoriteStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        try {
            $banners = Banner::where('is_active', true)->orderBy('order_position')->get();
            $categories = Category::where('is_active', true)->get();

            // Approved stores
            $featuredStores = Store::with('category')
                ->where('status', 'approved')
                ->orderByDesc('rating')
                ->take(6)
                ->get();

            // Popular products
            $popularProducts = Product::with(['store', 'variants'])
                ->where('is_active', true)
                ->whereHas('store', fn($q) => $q->where('status', 'approved'))
                ->orderByDesc('total_sales')
                ->take(8)
                ->get();

            // Promo/Discount products
            $discountProducts = Product::with(['store'])
                ->where('is_active', true)
                ->whereNotNull('compare_at_price')
                ->whereHas('store', fn($q) => $q->where('status', 'approved'))
                ->take(6)
                ->get();

            return view('home', compact('banners', 'categories', 'featuredStores', 'popularProducts', 'discountProducts'));
        } catch (\Throwable $e) {
            return response("HOME CONTROLLER EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine(), 200);
        }
    }

    public function explore(Request $request)
    {
        try {
            $query = $request->input('q');
            $categoryId = $request->input('category');
            $storeId = $request->input('store');
            $sort = $request->input('sort', 'latest');
            $minPrice = $request->input('min_price');
            $maxPrice = $request->input('max_price');
            $minRating = $request->input('min_rating');
            $onlyPromo = $request->boolean('promo');
            $onlyOpen = $request->boolean('open_only');

            $categories = Category::where('is_active', true)->get();

            $products = Product::with(['store', 'category'])
                ->where('is_active', true)
                ->whereHas('store', fn($q) => $q->where('status', 'approved'));

            if ($query) {
                $products->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%");
                });
            }

            if ($categoryId) {
                $products->where('category_id', $categoryId);
            }

            if ($storeId) {
                $products->where('store_id', $storeId);
            }

            if ($onlyPromo) {
                $products->whereNotNull('compare_at_price');
            }

            if ($onlyOpen) {
                $products->whereHas('store', fn($q) => $q->where('is_open', true));
            }

            if ($minPrice) {
                $products->where('price', '>=', (float) $minPrice);
            }

            if ($maxPrice) {
                $products->where('price', '<=', (float) $maxPrice);
            }

            if ($minRating) {
                $products->where('rating', '>=', (float) $minRating);
            }

            // Sorting
            if ($sort === 'price_asc') {
                $products->orderBy('price', 'asc');
            } elseif ($sort === 'price_desc') {
                $products->orderBy('price', 'desc');
            } elseif ($sort === 'rating') {
                $products->orderBy('rating', 'desc');
            } elseif ($sort === 'popular') {
                $products->orderBy('total_sales', 'desc');
            } else {
                $products->latest();
            }

            $products = $products->paginate(12)->withQueryString();

            $stores = Store::where('status', 'approved');
            if ($query) {
                $stores->where('name', 'like', "%{$query}%");
            }
            $stores = $stores->take(4)->get();

            return view('explore', compact('products', 'categories', 'query', 'categoryId', 'stores', 'sort', 'minPrice', 'maxPrice', 'minRating', 'onlyPromo', 'onlyOpen'));
        } catch (\Throwable $e) {
            return response("EXPLORE CONTROLLER EXCEPTION: " . $e->getMessage(), 200);
        }
    }

    public function storeShow(string $slug)
    {
        $store = Store::with(['category', 'products' => fn($q) => $q->where('is_active', true), 'reviews.buyer'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('store-show', compact('store'));
    }

    public function productShow(string $slug)
    {
        $product = Product::with(['store', 'category', 'variants', 'reviews.buyer'])
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedProducts = Product::where('store_id', $product->store_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->take(4)
            ->get();

        $isWishlisted = false;
        if (Auth::check()) {
            $isWishlisted = Wishlist::where('user_id', Auth::id())->where('product_id', $product->id)->exists();
        }

        return view('product-show', compact('product', 'relatedProducts', 'isWishlisted'));
    }

    public function helpCenter()
    {
        return view('help');
    }
}
