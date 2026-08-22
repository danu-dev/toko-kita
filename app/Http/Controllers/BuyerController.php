<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Address;
use App\Models\Store;
use App\Models\Wishlist;
use App\Models\FavoriteStore;
use App\Models\Review;
use App\Models\Dispute;
use App\Models\Coupon;
use App\Models\User;
use App\Services\CommissionService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BuyerController extends Controller
{
    protected OrderService $orderService;
    protected CommissionService $commissionService;
    protected PaymentService $paymentService;

    public function __construct(
        OrderService $orderService,
        CommissionService $commissionService,
        PaymentService $paymentService
    ) {
        $this->orderService = $orderService;
        $this->commissionService = $commissionService;
        $this->paymentService = $paymentService;
    }

    // CART
    public function viewCart()
    {
        $user = Auth::user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $items = CartItem::with(['product.store', 'variant'])
            ->where('cart_id', $cart->id)
            ->get();

        $groupedCart = $items->groupBy(fn($item) => $item->product->store_id);

        return view('buyer.cart', compact('groupedCart', 'items'));
    }

    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        $product = Product::with('store')->findOrFail($validated['product_id']);

        if (Auth::user()->store && Auth::user()->store->id === $product->store_id) {
            return back()->with('error', 'Anda tidak dapat membeli produk dari toko Anda sendiri.');
        }

        $requestedQty = (int) $validated['quantity'];
        if ($product->stock < $requestedQty || $product->stock <= 0) {
            return back()->with('error', "Maaf, stok produk '{$product->name}' tidak mencukupi (sisa {$product->stock} {$product->unit}).");
        }

        if (!empty($validated['product_variant_id'])) {
            $variant = ProductVariant::findOrFail($validated['product_variant_id']);
            if ($variant->stock < $requestedQty || $variant->stock <= 0) {
                return back()->with('error', "Maaf, stok varian '{$variant->name}' tidak mencukupi (sisa {$variant->stock}).");
            }
        }

        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $validated['product_id'])
            ->where('product_variant_id', $validated['product_variant_id'] ?? null)
            ->first();

        if ($item) {
            $newTotalQty = $item->quantity + $requestedQty;
            if ($product->stock < $newTotalQty) {
                return back()->with('error', "Jumlah total di keranjang ({$newTotalQty}) melebihi stok yang tersedia ({$product->stock} {$product->unit}).");
            }
            $item->quantity = $newTotalQty;
            if (!empty($validated['notes'])) {
                $item->notes = $validated['notes'];
            }
            $item->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $validated['product_id'],
                'product_variant_id' => $validated['product_variant_id'] ?? null,
                'quantity' => $requestedQty,
                'notes' => $validated['notes'] ?? null,
            ]);
        }

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }

    public function updateCartItem(Request $request, int $id)
    {
        $item = CartItem::with(['product', 'variant'])
            ->whereHas('cart', fn($q) => $q->where('user_id', Auth::id()))
            ->findOrFail($id);
        
        $action = $request->input('action');
        if ($action === 'increase') {
            if ($item->product->stock <= $item->quantity) {
                return back()->with('error', "Stok maksimal tercapai ({$item->product->stock} {$item->product->unit}).");
            }
            $item->increment('quantity');
        } elseif ($action === 'decrease') {
            if ($item->quantity > 1) {
                $item->decrement('quantity');
            } else {
                $item->delete();
            }
        } elseif ($action === 'delete') {
            $item->delete();
        }

        return back()->with('success', 'Keranjang diperbarui.');
    }

    // CHECKOUT
    public function checkout(Request $request, int $storeId)
    {
        $user = Auth::user();
        $store = Store::findOrFail($storeId);

        if ($user->store && $user->store->id === $store->id) {
            return redirect()->route('cart')->with('error', 'Anda tidak dapat melakukan checkout dari toko Anda sendiri.');
        }

        $cart = Cart::where('user_id', $user->id)->first();
        if (!$cart) {
            return redirect()->route('cart')->with('error', 'Keranjang kosong.');
        }

        $items = CartItem::with(['product', 'variant'])
            ->where('cart_id', $cart->id)
            ->whereHas('product', fn($q) => $q->where('store_id', $storeId))
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Tidak ada item dari toko ini.');
        }

        foreach ($items as $it) {
            if ($it->product->stock < $it->quantity) {
                return redirect()->route('cart')->with('error', "Stok '{$it->product->name}' tidak mencukupi (tersisa {$it->product->stock}). Silakan sesuaikan jumlah di keranjang.");
            }
        }

        $subtotal = $items->sum(fn($i) => $i->subtotal);
        $addresses = Address::where('user_id', $user->id)->get();
        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();

        $deliveryFee = 8000;
        $serviceFee = 1000;
        $total = $subtotal + $deliveryFee + $serviceFee;

        // Available Coupons
        $activeCoupons = Coupon::where('is_active', true)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->get();

        return view('buyer.checkout', compact('store', 'items', 'subtotal', 'addresses', 'defaultAddress', 'deliveryFee', 'serviceFee', 'total', 'user', 'activeCoupons'));
    }

    public function processCheckout(Request $request, int $storeId)
    {
        $validated = $request->validate([
            'fulfillment_type' => 'required|in:delivery,pickup',
            'address_id' => 'required_if:fulfillment_type,delivery|nullable|exists:addresses,id',
            'payment_method' => 'required|in:qris,gopay,ovo,dana,bca_va,mandiri_va,cod',
            'use_points' => 'nullable',
            'coupon_code' => 'nullable|string',
            'buyer_notes' => 'nullable|string|max:500',
        ]);

        $user = User::findOrFail(Auth::id());
        $store = Store::findOrFail($storeId);

        if ($user->store && $user->store->id === $store->id) {
            return redirect()->route('cart')->with('error', 'Anda tidak dapat melakukan pemesanan pada toko milik sendiri.');
        }

        $cart = Cart::where('user_id', $user->id)->firstOrFail();

        $items = CartItem::with(['product', 'variant'])
            ->where('cart_id', $cart->id)
            ->whereHas('product', fn($q) => $q->where('store_id', $storeId))
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Item keranjang tidak valid.');
        }

        foreach ($items as $item) {
            if ($item->product->stock < $item->quantity) {
                return redirect()->route('cart')->with('error', "Gagal memproses pesanan: Stok '{$item->product->name}' tidak mencukupi (tersisa {$item->product->stock} {$item->product->unit}).");
            }
        }

        $subtotal = $items->sum(fn($i) => $i->subtotal);
        $deliveryFee = ($validated['fulfillment_type'] === 'delivery') ? 8000 : 0;
        $serviceFee = 1000;
        
        // 1. Coupon Discount Calculation
        $couponDiscount = 0;
        if (!empty($validated['coupon_code'])) {
            $coupon = Coupon::where('code', strtoupper($validated['coupon_code']))->first();
            if ($coupon) {
                $couponDiscount = $coupon->calculateDiscount($subtotal);
            }
        }

        // 2. Point deduction logic (1 point = Rp 1 discount)
        $pointDiscount = 0;
        $pointsToDeduct = 0;
        $usePointsRequested = $request->filled('use_points') && in_array((string)$request->input('use_points'), ['1', 'true', 'on']);
        
        $grossRemaining = max(0, ($subtotal + $deliveryFee + $serviceFee) - $couponDiscount);
        if ($usePointsRequested && $user->loyalty_points > 0) {
            $maxPointDiscount = max(0, $grossRemaining - 1000);
            $pointDiscount = min((float)$user->loyalty_points, (float)$maxPointDiscount);
            $pointsToDeduct = (int) $pointDiscount;
        }

        $totalDiscount = $couponDiscount + $pointDiscount;
        $commissionFee = $this->commissionService->calculate($subtotal);
        $sellerEarnings = $this->commissionService->calculateSellerEarnings($subtotal);
        $total = max(1000, ($subtotal + $deliveryFee + $serviceFee) - $totalDiscount);

        DB::beginTransaction();
        try {
            $orderNumber = 'TK-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            $order = Order::create([
                'order_number' => $orderNumber,
                'buyer_id' => $user->id,
                'store_id' => $store->id,
                'address_id' => $validated['fulfillment_type'] === 'delivery' ? $validated['address_id'] : null,
                'fulfillment_type' => $validated['fulfillment_type'],
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'service_fee' => $serviceFee,
                'discount_amount' => $totalDiscount,
                'commission_fee' => $commissionFee,
                'seller_earnings' => $sellerEarnings,
                'total' => $total,
                'status' => 'menunggu_konfirmasi',
                'buyer_notes' => $validated['buyer_notes'] ?? null,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'variant_name' => $item->variant?->name,
                    'price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                    'notes' => $item->notes,
                ]);

                $item->product->decrement('stock', $item->quantity);
            }

            // Deduct loyalty points
            if ($pointsToDeduct > 0) {
                $user->decrement('loyalty_points', $pointsToDeduct);
            }

            $this->paymentService->createPayment($order, $validated['payment_method']);

            $order->statusHistories()->create([
                'from_status' => null,
                'to_status' => 'menunggu_konfirmasi',
                'changed_by' => $user->id,
                'notes' => 'Pesanan berhasil dibuat.' . ($totalDiscount > 0 ? " (Diskon Rp " . number_format($totalDiscount, 0, ',', '.') . ")" : ''),
            ]);

            CartItem::whereIn('id', $items->pluck('id'))->delete();

            DB::commit();

            return redirect()->route('orders.track', $order->id)->with('success', 'Pesanan berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage());
        }
    }

    // ORDERS & TRACKING
    public function ordersIndex(Request $request)
    {
        $status = $request->input('status');

        $query = Order::with(['store', 'items.product', 'payment'])
            ->where('buyer_id', Auth::id());

        if ($status && in_array($status, ['menunggu_konfirmasi', 'diproses', 'siap_diambil_dikirim', 'selesai', 'dibatalkan', 'retur_refund'])) {
            $query->where('status', $status);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('buyer.orders-index', compact('orders', 'status'));
    }

    public function trackOrder(int $id)
    {
        $order = Order::with(['store', 'items.product', 'statusHistories.user', 'payment', 'address', 'review', 'dispute'])
            ->where('buyer_id', Auth::id())
            ->findOrFail($id);

        return view('buyer.order-track', compact('order'));
    }

    public function invoiceView(int $id)
    {
        $order = Order::with(['store.user', 'items.product', 'payment', 'address', 'buyer'])
            ->where('buyer_id', Auth::id())
            ->findOrFail($id);

        return view('buyer.order-invoice', compact('order'));
    }

    public function cancelOrder(Request $request, int $id)
    {
        $order = Order::where('buyer_id', Auth::id())->findOrFail($id);
        $reason = $request->input('reason', 'Dibatalkan oleh pembeli');

        try {
            $this->orderService->transition($order, OrderService::STATUS_DIBATALKAN, Auth::user(), $reason);
            return back()->with('success', 'Pesanan berhasil dibatalkan dan stok dikembalikan.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function completeOrder(int $id)
    {
        $order = Order::where('buyer_id', Auth::id())->findOrFail($id);

        try {
            $this->orderService->transition($order, OrderService::STATUS_SELESAI, Auth::user(), 'Pesanan dikonfirmasi selesai oleh pembeli.');
            return back()->with('success', 'Terima kasih! Pesanan telah selesai. Silakan beri ulasan toko.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // REVIEWS & DISPUTES
    public function storeReview(Request $request, int $id)
    {
        $order = Order::where('buyer_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        Review::updateOrCreate(
            ['order_id' => $order->id],
            [
                'buyer_id' => Auth::id(),
                'store_id' => $order->store_id,
                'product_id' => $order->items->first()?->product_id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
            ]
        );

        $store = $order->store;
        $avg = Review::where('store_id', $store->id)->avg('rating');
        $count = Review::where('store_id', $store->id)->count();
        $store->update(['rating' => $avg, 'total_reviews' => $count]);

        return back()->with('success', 'Ulasan berhasil dikirim!');
    }

    public function fileDispute(Request $request, int $id)
    {
        $order = Order::where('buyer_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
        ]);

        Dispute::create([
            'order_id' => $order->id,
            'buyer_id' => Auth::id(),
            'store_id' => $order->store_id,
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'status' => 'opened',
        ]);

        return back()->with('success', 'Komplain berhasil diajukan. Tim admin & penjual akan segera merespon.');
    }

    // WISHLIST & FAVORITES
    public function wishlist()
    {
        $wishlists = Wishlist::with('product.store')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        $favoriteStores = FavoriteStore::with('store.category')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('buyer.wishlist', compact('wishlists', 'favoriteStores'));
    }

    public function toggleWishlist(Request $request, int $productId)
    {
        $user = Auth::user();
        $exists = Wishlist::where('user_id', $user->id)->where('product_id', $productId)->first();

        if ($exists) {
            $exists->delete();
            $msg = 'Dihapus dari wishlist';
        } else {
            Wishlist::create(['user_id' => $user->id, 'product_id' => $productId]);
            $msg = 'Ditambahkan ke wishlist!';
        }

        return back()->with('success', $msg);
    }

    public function toggleFavoriteStore(Request $request, int $storeId)
    {
        $user = Auth::user();
        $store = Store::findOrFail($storeId);

        $exists = FavoriteStore::where('user_id', $user->id)->where('store_id', $store->id)->first();

        if ($exists) {
            $exists->delete();
            $isFavorited = false;
            $msg = "Toko '{$store->name}' dihapus dari toko favorit.";
        } else {
            FavoriteStore::create([
                'user_id' => $user->id,
                'store_id' => $store->id,
            ]);
            $isFavorited = true;
            $msg = "Toko '{$store->name}' berhasil ditambahkan ke toko favorit!";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_favorited' => $isFavorited,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    // PROFILE & ADDRESSES
    public function profile()
    {
        $user = Auth::user();
        $addresses = Address::where('user_id', $user->id)->get();
        return view('buyer.profile', compact('user', 'addresses'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
        ]);

        $user->update($validated);
        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function storeAddress(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'address_line' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notes' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        if ($request->boolean('is_default')) {
            Address::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        Address::create([
            'user_id' => Auth::id(),
            'label' => $validated['label'],
            'recipient_name' => $validated['recipient_name'],
            'recipient_phone' => $validated['recipient_phone'],
            'address_line' => $validated['address_line'],
            'city' => $validated['city'],
            'latitude' => $validated['latitude'] ?? -7.946714,
            'longitude' => $validated['longitude'] ?? 112.615668,
            'notes' => $validated['notes'] ?? null,
            'is_default' => $request->boolean('is_default') || (Address::where('user_id', Auth::id())->count() === 0),
        ]);

        return back()->with('success', 'Titik alamat baru berhasil disimpan!');
    }
}
