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
        $minRating = $request->input('min_rating');
        $onlyPromo = $request->boolean('promo');
        $onlyOpen = $request->boolean('open_only');

        $categories = Category::where('is_active', true)->get();

        $products = Product::with(['store', 'category'])
            ->where('is_active', true)
            ->whereHas('store', function($q) use ($onlyOpen) {
                $q->where('status', 'approved');
                if ($onlyOpen) {
                    $q->where('is_open', true);
                }
            });

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

        if ($minRating !== null && $minRating !== '') {
            $products->where('rating', '>=', (float)$minRating);
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
            'minRating',
            'onlyPromo',
            'onlyOpen'
        ));
    }

    public function storeShow(string $slug)
    {
        $store = Store::with(['category', 'products' => fn($q) => $q->where('is_active', true), 'reviews.buyer'])
            ->where('slug', $slug)
            ->firstOrFail();

        $isFavorited = false;
        if (Auth::check()) {
            $isFavorited = FavoriteStore::where('user_id', Auth::id())
                ->where('store_id', $store->id)
                ->exists();
        }

        $totalFollowers = FavoriteStore::where('store_id', $store->id)->count();

        return view('store-show', compact('store', 'isFavorited', 'totalFollowers'));
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
        $faqs = [
            [
                'q' => 'Bagaimana cara memesan makanan/produk di TokoKita?',
                'a' => 'Cari produk atau warung terdekat melalui halaman Beranda atau Jelajah. Pilih menu, varian, dan porsi yang diinginkan, masukkan ke keranjang, lalu klik Checkout untuk memilih metode Ambil Sendiri (Pickup) atau Pesan Antar Kurir Lokal.'
            ],
            [
                'q' => 'Apakah sistem pembayaran di TokoKita aman?',
                'a' => 'Sangat aman. TokoKita menggunakan sistem Escrow (Rekening Bersama). Dana pembayaran Anda ditampung sementara dan baru diteruskan ke saldo toko penjual setelah pesanan selesai diterima dengan baik.'
            ],
            [
                'q' => 'Bagaimana jika pesanan saya tidak sesuai atau bermasalah?',
                'a' => 'Anda dapat mengajukan Komplain / Pengembalian (Dispute) dari detail pesanan. Tim Admin TokoKita akan memediasi dan memproses pengembalian dana (refund) jika komplain terbukti sah.'
            ],
            [
                'q' => 'Bagaimana cara mendaftar menjadi Mitra Penjual UMKM?',
                'a' => 'Klik menu "Buka Warung / Toko" atau tombol "Gabung Jadi Mitra UMKM", lengkapi profil toko Anda, alamat peta, jam operasional, dan unggah foto menu. Toko Anda akan segera aktif melayani pembeli di sekitar wilayah Anda.'
            ],
            [
                'q' => 'Berapa biaya komisi platform untuk Mitra Penjual?',
                'a' => 'TokoKita menerapkan biaya komisi platform sangat rendah hanya 5% dari total penjualan, memastikan keuntungan maksimal bagi kemajuan pelaku UMKM lokal.'
            ]
        ];

        return view('help-center', compact('faqs'));
    }
}
