<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Store;
use App\Models\Product;
use App\Models\Review;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
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
    }

    public function explore(Request $request)
    {
        $query = $request->input('q');
        $categoryId = $request->input('category');
        $storeId = $request->input('store');
        $sort = $request->input('sort', 'latest'); // latest, price_asc, price_desc, rating, popular
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $onlyPromo = $request->boolean('promo');

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

        if ($minPrice !== null && $minPrice !== '') {
            $products->where('price', '>=', (float)$minPrice);
        }

        if ($maxPrice !== null && $maxPrice !== '') {
            $products->where('price', '<=', (float)$maxPrice);
        }

        if ($onlyPromo) {
            $products->whereNotNull('compare_at_price');
        }

        // Sorting
        switch ($sort) {
            case 'price_asc':
                $products->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $products->orderBy('price', 'desc');
                break;
            case 'rating':
                $products->orderByDesc('rating');
                break;
            case 'popular':
                $products->orderByDesc('total_sales');
                break;
            default:
                $products->latest();
                break;
        }

        $products = $products->paginate(12)->withQueryString();

        $stores = Store::where('status', 'approved');
        if ($query) {
            $stores->where('name', 'like', "%{$query}%");
        }
        $stores = $stores->take(4)->get();

        return view('explore', compact(
            'products',
            'categories',
            'query',
            'categoryId',
            'stores',
            'sort',
            'minPrice',
            'maxPrice',
            'onlyPromo'
        ));
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
}
