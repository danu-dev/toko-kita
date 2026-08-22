<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Store;
use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Services\OrderService;
use App\Services\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TokoKitaFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_homepage_loads_successfully()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Toko');
        $response->assertSee('Kita.');
        $response->assertSee('Warung Nasi Bu Siti');
    }

    public function test_explore_page_filters_by_category()
    {
        $category = Category::where('slug', 'kuliner-makanan')->first();
        $response = $this->get('/jelajah?category=' . $category->id);
        $response->assertStatus(200);
        $response->assertSee('Rawon Daging Sapi Spesial');
    }

    public function test_explore_page_filters_by_min_rating()
    {
        $response = $this->get('/jelajah?min_rating=4.5');
        $response->assertStatus(200);
        $response->assertViewHas('minRating', '4.5');
    }

    public function test_product_detail_page_loads()
    {
        $product = Product::where('slug', 'rawon-daging-sapi-spesial')->first();
        $response = $this->get('/produk/' . $product->slug);
        $response->assertStatus(200);
        $response->assertSee('Rawon Daging Sapi Spesial');
        $response->assertSee('Estimasi Sampai');
        $response->assertSee('15 - 30 Menit');
        $response->assertSee('Bagikan Produk');
    }

    public function test_buyer_can_add_to_cart_and_checkout()
    {
        $buyer = User::where('email', 'buyer@tokokita.id')->first();
        $store = Store::where('slug', 'warung-nasi-bu-siti')->first();
        $product = Product::where('store_id', $store->id)->first();

        $this->actingAs($buyer);

        $cartResponse = $this->post('/keranjang/tambah', [
            'product_id' => $product->id,
            'quantity' => 2,
            'notes' => 'Tolong kuahnya banyakin',
        ]);
        $cartResponse->assertRedirect();

        $viewCart = $this->get('/keranjang');
        $viewCart->assertStatus(200);
        $viewCart->assertSee($product->name);

        $checkoutResponse = $this->post('/checkout/' . $store->id, [
            'fulfillment_type' => 'delivery',
            'address_id' => $buyer->defaultAddress->id,
            'payment_method' => 'qris',
            'buyer_notes' => 'Pagar warna hitam',
        ]);

        $checkoutResponse->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'buyer_id' => $buyer->id,
            'store_id' => $store->id,
            'status' => 'menunggu_konfirmasi',
        ]);
    }

    public function test_buyer_can_redeem_loyalty_points_for_discount()
    {
        $buyer = User::where('email', 'buyer@tokokita.id')->first();
        $buyer->loyalty_points = 5000;
        $buyer->save();

        $store = Store::where('slug', 'warung-nasi-bu-siti')->first();
        $product = Product::where('store_id', $store->id)->first();

        $this->actingAs($buyer->fresh());

        $cart = Cart::firstOrCreate(['user_id' => $buyer->id]);
        CartItem::where('cart_id', $cart->id)->delete();

        $this->post('/keranjang/tambah', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->post('/checkout/' . $store->id, [
            'fulfillment_type' => 'pickup',
            'payment_method' => 'qris',
            'use_points' => '1',
        ]);

        $response->assertRedirect();
        $order = Order::where('buyer_id', $buyer->id)->latest('id')->first();
        $this->assertEquals(5000, (int)$order->discount_amount);
        $this->assertEquals(0, $buyer->fresh()->loyalty_points);
    }

    public function test_buyer_can_apply_coupon_promo()
    {
        $buyer = User::where('email', 'buyer@tokokita.id')->first();
        $store = Store::where('slug', 'warung-nasi-bu-siti')->first();
        $product = Product::where('store_id', $store->id)->first();

        Coupon::create([
            'code' => 'UMKMHEMAT',
            'title' => 'Diskon 20%',
            'type' => 'percent',
            'discount_value' => 20,
            'max_discount' => 10000,
            'is_active' => true,
        ]);

        $this->actingAs($buyer);

        $cart = Cart::firstOrCreate(['user_id' => $buyer->id]);
        CartItem::where('cart_id', $cart->id)->delete();

        $this->post('/keranjang/tambah', [
            'product_id' => $product->id,
            'quantity' => 1, // 28.000
        ]);

        $response = $this->post('/checkout/' . $store->id, [
            'fulfillment_type' => 'pickup',
            'payment_method' => 'qris',
            'coupon_code' => 'UMKMHEMAT',
        ]);

        $response->assertRedirect();
        $order = Order::where('buyer_id', $buyer->id)->latest('id')->first();
        // 20% of 28000 = 5600
        $this->assertEquals(5600, (int)$order->discount_amount);
    }

    public function test_seller_cannot_buy_from_own_store()
    {
        $seller = User::where('email', 'seller@tokokita.id')->first();
        $store = Store::where('user_id', $seller->id)->first();
        $product = Product::where('store_id', $store->id)->first();

        $this->actingAs($seller);

        $response = $this->post('/keranjang/tambah', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('cart_items', [
            'product_id' => $product->id,
        ]);
    }

    public function test_stock_validation_prevents_overselling()
    {
        $buyer = User::where('email', 'buyer@tokokita.id')->first();
        $store = Store::where('slug', 'warung-nasi-bu-siti')->first();
        $product = Product::where('store_id', $store->id)->first();
        $product->update(['stock' => 2]);

        $this->actingAs($buyer);

        $response = $this->post('/keranjang/tambah', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $response->assertSessionHas('error');
    }

    public function test_order_state_machine_transitions()
    {
        $orderService = app(OrderService::class);
        $seller = User::where('email', 'seller@tokokita.id')->first();
        $buyer = User::where('email', 'buyer@tokokita.id')->first();
        $store = Store::where('user_id', $seller->id)->first();

        $order = Order::create([
            'order_number' => 'TK-TEST-001',
            'buyer_id' => $buyer->id,
            'store_id' => $store->id,
            'fulfillment_type' => 'delivery',
            'subtotal' => 50000,
            'delivery_fee' => 8000,
            'service_fee' => 1000,
            'discount_amount' => 0,
            'commission_fee' => 2500,
            'seller_earnings' => 47500,
            'total' => 59000,
            'status' => 'menunggu_konfirmasi',
        ]);

        $orderService->transition($order, OrderService::STATUS_DIPROSES, $seller, 'Penjual memproses makanan.');
        $this->assertEquals('diproses', $order->fresh()->status);

        $orderService->transition($order, OrderService::STATUS_SIAP, $seller, 'Makanan siap dikirim.');
        $this->assertEquals('siap_diambil_dikirim', $order->fresh()->status);

        $orderService->transition($order, OrderService::STATUS_SELESAI, $order->buyer, 'Pesanan sampai tujuan.');
        $this->assertEquals('selesai', $order->fresh()->status);
    }

    public function test_seller_dashboard_access_and_security()
    {
        $seller = User::where('email', 'seller@tokokita.id')->first();
        $buyer = User::where('email', 'buyer@tokokita.id')->first();

        $this->actingAs($seller);
        $sellerRes = $this->get('/mitra/dashboard');
        $sellerRes->assertStatus(200);
        $sellerRes->assertSee('Ringkasan Toko');

        $this->actingAs($buyer);
        $buyerRes = $this->get('/mitra/dashboard');
        $buyerRes->assertStatus(403);
    }

    public function test_admin_dashboard_and_verification()
    {
        $admin = User::where('email', 'admin@tokokita.id')->first();
        $pendingStore = Store::where('status', 'pending')->first();

        $this->actingAs($admin);
        $adminRes = $this->get('/admin/dashboard');
        $adminRes->assertStatus(200);
        $adminRes->assertSee('Platform Executive Dashboard');

        $approveRes = $this->post('/admin/mitra/' . $pendingStore->id . '/verifikasi', [
            'action' => 'approve'
        ]);
        $approveRes->assertRedirect();
        $this->assertEquals('approved', $pendingStore->fresh()->status);
    }

    public function test_buyer_can_favorite_and_unfavorite_store()
    {
        $buyer = User::where('email', 'buyer@tokokita.id')->first();
        $store = Store::where('slug', 'warung-nasi-bu-siti')->first();

        $this->actingAs($buyer);

        // Toggle Favorite: Add
        $res = $this->post(route('stores.favorite.toggle', $store->id));
        $res->assertRedirect();
        $this->assertDatabaseHas('favorite_stores', [
            'user_id' => $buyer->id,
            'store_id' => $store->id,
        ]);

        // Verify on wishlist page
        $wishlistRes = $this->get(route('wishlist'));
        $wishlistRes->assertStatus(200);
        $wishlistRes->assertSee($store->name);

        // Toggle Favorite: Remove
        $unfavRes = $this->post(route('stores.favorite.toggle', $store->id));
        $unfavRes->assertRedirect();
        $this->assertDatabaseMissing('favorite_stores', [
            'user_id' => $buyer->id,
            'store_id' => $store->id,
        ]);
    }

    public function test_buyer_can_view_digital_invoice_and_receipt()
    {
        $buyer = User::where('email', 'buyer@tokokita.id')->first();
        $store = Store::where('slug', 'warung-nasi-bu-siti')->first();

        $order = Order::where('buyer_id', $buyer->id)->first();
        if (!$order) {
            $order = Order::create([
                'order_number' => 'TK-TEST-INV01',
                'buyer_id' => $buyer->id,
                'store_id' => $store->id,
                'fulfillment_type' => 'pickup',
                'subtotal' => 50000,
                'delivery_fee' => 0,
                'service_fee' => 1000,
                'discount_amount' => 0,
                'commission_fee' => 5000,
                'seller_earnings' => 45000,
                'total' => 51000,
                'status' => 'selesai',
            ]);
        }

        $this->actingAs($buyer);

        $response = $this->get(route('orders.invoice', $order->id));
        $response->assertStatus(200);
        $response->assertSee('Struk Digital');
        $response->assertSee($order->order_number);
        $response->assertSee($order->store->name);
    }

    public function test_store_open_status_displayed()
    {
        $store = Store::where('slug', 'warung-nasi-bu-siti')->first();
        $response = $this->get('/toko/' . $store->slug);
        $response->assertStatus(200);
        $response->assertSee('Status Operasional');
        $response->assertSee('Buka Sekarang');
    }

    public function test_admin_can_manage_coupons()
    {
        $admin = User::where('email', 'admin@tokokita.id')->first();
        $this->actingAs($admin);

        $code = 'TEST' . rand(1000, 9999);
        $createRes = $this->post(route('admin.coupons.store'), [
            'code' => $code,
            'title' => 'Diskon Pengguna Baru',
            'type' => 'fixed',
            'discount_value' => 15000,
            'min_order_amount' => 40000,
        ]);
        $createRes->assertRedirect();
        $this->assertDatabaseHas('coupons', ['code' => $code]);

        $coupon = Coupon::where('code', $code)->first();
        $toggleRes = $this->post(route('admin.coupons.toggle', $coupon->id));
        $toggleRes->assertRedirect();
        $this->assertFalse($coupon->fresh()->is_active);

        $deleteRes = $this->delete(route('admin.coupons.delete', $coupon->id));
        $deleteRes->assertRedirect();
        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }

    public function test_seller_can_toggle_product_status()
    {
        $seller = User::where('email', 'seller@tokokita.id')->first();
        $store = Store::where('user_id', $seller->id)->first();
        $product = Product::where('store_id', $store->id)->first();

        $this->actingAs($seller);

        $initialStatus = $product->is_active;
        $response = $this->post(route('seller.products.toggle', $product->id));
        $response->assertRedirect();

        $this->assertEquals(!$initialStatus, $product->fresh()->is_active);
    }

    public function test_seller_reports_display_top_products()
    {
        $seller = User::where('email', 'seller@tokokita.id')->first();
        $this->actingAs($seller);

        $response = $this->get(route('seller.reports'));
        $response->assertStatus(200);
        $response->assertSee('Top 5 Menu / Produk Terlaris');
    }

    public function test_buyer_can_filter_orders_by_status()
    {
        $buyer = User::where('email', 'buyer@tokokita.id')->first();
        $this->actingAs($buyer);

        $response = $this->get(route('orders.index', ['status' => 'selesai']));
        $response->assertStatus(200);
        $response->assertViewHas('status', 'selesai');
    }
}

