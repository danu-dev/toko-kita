<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatController;

/*
|--------------------------------------------------------------------------
| Web Routes — Toko Kita Hyperlocal Marketplace (PRD Spec Section 5.3)
|--------------------------------------------------------------------------
*/

// Public & Buyer Homepage/Catalog
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/jelajah', [HomeController::class, 'explore'])->name('explore');
Route::get('/toko/{slug}', [HomeController::class, 'storeShow'])->name('stores.show');
Route::get('/produk/{slug}', [HomeController::class, 'productShow'])->name('products.show');

// Authentication & Quick Role Switcher
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
Route::post('/daftar', [AuthController::class, 'register']);
Route::get('/mitra/daftar', [AuthController::class, 'showSellerRegister'])->name('seller.register');
Route::post('/mitra/daftar', [AuthController::class, 'sellerRegister'])->name('seller.register.submit');
Route::get('/auth/quick-login/{role}', [AuthController::class, 'quickLogin'])->name('auth.quick-login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Buyer Authenticated Routes
Route::middleware(['auth'])->group(function () {
    // Cart & Multi-Cart Checkout
    Route::get('/keranjang', [BuyerController::class, 'viewCart'])->name('cart');
    Route::post('/keranjang/tambah', [BuyerController::class, 'addToCart'])->name('cart.add');
    Route::post('/keranjang/update/{id}', [BuyerController::class, 'updateCartItem'])->name('cart.update');
    Route::get('/checkout/{storeId}', [BuyerController::class, 'checkout'])->name('checkout');
    Route::post('/checkout/{storeId}', [BuyerController::class, 'processCheckout'])->name('checkout.process');

    // Orders, Tracking & Status Pulse
    Route::get('/pesanan', [BuyerController::class, 'ordersIndex'])->name('orders.index');
    Route::get('/pesanan/{id}/lacak', [BuyerController::class, 'trackOrder'])->name('orders.track');
    Route::get('/pesanan/{id}/invoice', [BuyerController::class, 'invoiceView'])->name('orders.invoice');
    Route::post('/pesanan/{id}/batal', [BuyerController::class, 'cancelOrder'])->name('orders.cancel');
    Route::post('/pesanan/{id}/selesai', [BuyerController::class, 'completeOrder'])->name('orders.complete');
    Route::post('/pesanan/{id}/ulasan', [BuyerController::class, 'storeReview'])->name('orders.review');
    Route::post('/pesanan/{id}/dispute', [BuyerController::class, 'fileDispute'])->name('orders.dispute');

    // Wishlist & Profile
    Route::get('/wishlist', [BuyerController::class, 'wishlist'])->name('wishlist');
    Route::post('/wishlist/toggle/{productId}', [BuyerController::class, 'toggleWishlist'])->name('wishlist.toggle');
    Route::post('/toko/{storeId}/favorit', [BuyerController::class, 'toggleFavoriteStore'])->name('stores.favorite.toggle');
    Route::get('/profil', [BuyerController::class, 'profile'])->name('profile');
    Route::post('/profil', [BuyerController::class, 'updateProfile'])->name('profile.update');
    Route::post('/alamat', [BuyerController::class, 'storeAddress'])->name('addresses.store');

    // Private Peer-to-Peer Chat (Buyer <-> Seller)
    Route::get('/chat', [ChatController::class, 'index'])->name('chats.index');
    Route::get('/chat/{id}', [ChatController::class, 'show'])->name('chats.show');
    Route::post('/chat/mulai', [ChatController::class, 'startChat'])->name('chats.start');
});

// Seller (Mitra UMKM) Routes - Strict Seller Boundary
Route::middleware(['auth', 'role:seller'])->prefix('mitra')->name('seller.')->group(function () {
    Route::get('/dashboard', [SellerController::class, 'dashboard'])->name('dashboard');
    Route::get('/pesanan', [SellerController::class, 'orders'])->name('orders');
    Route::post('/pesanan/{id}/status', [SellerController::class, 'updateOrderStatus'])->name('orders.status');

    Route::get('/produk', [SellerController::class, 'products'])->name('products');
    Route::get('/produk/tambah', [SellerController::class, 'createProduct'])->name('products.create');
    Route::post('/produk', [SellerController::class, 'storeProduct'])->name('products.store');
    Route::get('/produk/{id}/edit', [SellerController::class, 'editProduct'])->name('products.edit');
    Route::put('/produk/{id}', [SellerController::class, 'updateProduct'])->name('products.update');
    Route::delete('/produk/{id}', [SellerController::class, 'deleteProduct'])->name('products.delete');

    Route::get('/dompet', [SellerController::class, 'wallet'])->name('wallet');
    Route::post('/dompet/tarik', [SellerController::class, 'requestWithdrawal'])->name('wallet.withdraw');

    Route::get('/laporan', [SellerController::class, 'reports'])->name('reports');
    Route::get('/laporan/export-csv', [SellerController::class, 'exportReportsCsv'])->name('reports.export-csv');
    Route::post('/ulasan/{id}/balas', [SellerController::class, 'replyReview'])->name('reviews.reply');

    Route::get('/pengaturan', [SellerController::class, 'settings'])->name('settings');
    Route::put('/pengaturan', [SellerController::class, 'updateSettings'])->name('settings.update');
});

// Admin Operations Routes - Strict Platform Oversight Boundary
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    Route::get('/mitra/verifikasi', [AdminController::class, 'verifications'])->name('verifications');
    Route::post('/mitra/{id}/verifikasi', [AdminController::class, 'processVerification'])->name('verifications.process');

    Route::get('/transaksi', [AdminController::class, 'transactions'])->name('transactions');

    Route::get('/kategori', [AdminController::class, 'categories'])->name('categories');
    Route::post('/kategori', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::delete('/kategori/{id}', [AdminController::class, 'deleteCategory'])->name('categories.delete');

    Route::get('/dispute', [AdminController::class, 'disputes'])->name('disputes');
    Route::post('/dispute/{id}/resolusi', [AdminController::class, 'resolveDispute'])->name('disputes.resolve');

    Route::get('/pencairan', [AdminController::class, 'withdrawals'])->name('withdrawals');
    Route::post('/pencairan/{id}/proses', [AdminController::class, 'processWithdrawal'])->name('withdrawals.process');

    Route::get('/pengaturan', [AdminController::class, 'settings'])->name('settings');
    Route::post('/pengaturan', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::post('/banner', [AdminController::class, 'storeBanner'])->name('banners.store');
    Route::delete('/banner/{id}', [AdminController::class, 'deleteBanner'])->name('banners.delete');
    Route::post('/kupon', [AdminController::class, 'storeCoupon'])->name('coupons.store');
    Route::post('/kupon/{id}/toggle', [AdminController::class, 'toggleCoupon'])->name('coupons.toggle');
    Route::delete('/kupon/{id}', [AdminController::class, 'deleteCoupon'])->name('coupons.delete');
});
