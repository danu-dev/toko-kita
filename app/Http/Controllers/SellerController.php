<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Models\Review;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SellerController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    private function getStore(): ?Store
    {
        return Store::where('user_id', Auth::id())->first();
    }

    public function dashboard()
    {
        $store = $this->getStore();
        if (!$store) {
            return redirect()->route('seller.register');
        }

        // Metrics
        $totalOrders = Order::where('store_id', $store->id)->count();
        $activeOrders = Order::where('store_id', $store->id)
            ->whereIn('status', ['menunggu_konfirmasi', 'diproses', 'siap_diambil_dikirim'])
            ->count();
        $completedOrders = Order::where('store_id', $store->id)->where('status', 'selesai')->count();
        $totalRevenue = Order::where('store_id', $store->id)->where('status', 'selesai')->sum('seller_earnings');

        // Incoming unconfirmed orders
        $incomingOrders = Order::with(['buyer', 'items.product', 'payment'])
            ->where('store_id', $store->id)
            ->where('status', 'menunggu_konfirmasi')
            ->latest()
            ->get();

        // Recent Orders
        $recentOrders = Order::with(['buyer', 'items.product', 'payment'])
            ->where('store_id', $store->id)
            ->latest()
            ->take(5)
            ->get();

        $wallet = Wallet::firstOrCreate(['store_id' => $store->id]);

        return view('seller.dashboard', compact('store', 'totalOrders', 'activeOrders', 'completedOrders', 'totalRevenue', 'incomingOrders', 'recentOrders', 'wallet'));
    }

    public function orders(Request $request)
    {
        $store = $this->getStore();
        $status = $request->input('status');

        $orders = Order::with(['buyer', 'items.product', 'payment', 'statusHistories'])
            ->where('store_id', $store->id);

        if ($status) {
            $orders->where('status', $status);
        }

        $orders = $orders->latest()->paginate(10)->withQueryString();

        return view('seller.orders', compact('store', 'orders', 'status'));
    }

    public function updateOrderStatus(Request $request, int $id)
    {
        $store = $this->getStore();
        $order = Order::where('store_id', $store->id)->findOrFail($id);

        $action = $request->input('action'); // accept, ready, complete, reject
        $reason = $request->input('reason');

        try {
            if ($action === 'accept') {
                $this->orderService->transition($order, OrderService::STATUS_DIPROSES, Auth::user(), 'Pesanan diterima penjual.');
                $msg = 'Pesanan diterima dan mulai diproses!';
            } elseif ($action === 'ready') {
                $this->orderService->transition($order, OrderService::STATUS_SIAP, Auth::user(), 'Pesanan telah selesai disiapkan & siap diantar/diambil.');
                $msg = 'Status pesanan diubah ke Siap Diambil/Dikirim!';
            } elseif ($action === 'complete') {
                $this->orderService->transition($order, OrderService::STATUS_SELESAI, Auth::user(), 'Pesanan diselesaikan.');
                $msg = 'Pesanan selesai! Dana masuk ke saldo toko Anda.';
            } elseif ($action === 'reject') {
                $this->orderService->transition($order, OrderService::STATUS_DIBATALKAN, Auth::user(), $reason ?: 'Penjual menolak pesanan.');
                $msg = 'Pesanan ditolak.';
            } else {
                return back()->with('error', 'Aksi tidak valid.');
            }

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // PRODUCTS (Supports Local File Upload OR Image URL)
    public function products()
    {
        $store = $this->getStore();
        $products = Product::with(['category', 'variants'])
            ->where('store_id', $store->id)
            ->latest()
            ->paginate(12);

        return view('seller.products.index', compact('store', 'products'));
    }

    public function createProduct()
    {
        $store = $this->getStore();
        $categories = Category::where('is_active', true)->get();
        return view('seller.products.create', compact('store', 'categories'));
    }

    public function storeProduct(Request $request)
    {
        $store = $this->getStore();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'required|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'image_url' => 'nullable|url',
        ]);

        $imagePath = $validated['image_url'] ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80';
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('products', 'public');
            $imagePath = '/storage/' . $path;
        }

        $product = Product::create([
            'store_id' => $store->id,
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . rand(1000, 9999),
            'description' => $validated['description'],
            'price' => $validated['price'],
            'compare_at_price' => $validated['compare_at_price'] ?? null,
            'stock' => $validated['stock'],
            'unit' => $validated['unit'],
            'image' => $imagePath,
            'is_active' => true,
        ]);

        if ($request->has('variant_name')) {
            foreach ($request->input('variant_name') as $index => $vName) {
                if (!empty($vName)) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'name' => $vName,
                        'price_modifier' => (float) ($request->input('variant_price')[$index] ?? 0),
                        'stock' => (int) ($request->input('variant_stock')[$index] ?? 50),
                    ]);
                }
            }
        }

        return redirect()->route('seller.products')->with('success', 'Produk berhasil ditambahkan!');
    }

    public function editProduct(int $id)
    {
        $store = $this->getStore();
        $product = Product::with('variants')->where('store_id', $store->id)->findOrFail($id);
        $categories = Category::where('is_active', true)->get();

        return view('seller.products.edit', compact('store', 'product', 'categories'));
    }

    public function updateProduct(Request $request, int $id)
    {
        $store = $this->getStore();
        $product = Product::where('store_id', $store->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'required|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'image_url' => 'nullable|url',
            'is_active' => 'nullable|boolean',
        ]);

        $imagePath = $product->image;
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('products', 'public');
            $imagePath = '/storage/' . $path;
        } elseif (!empty($validated['image_url'])) {
            $imagePath = $validated['image_url'];
        }

        $product->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'price' => $validated['price'],
            'compare_at_price' => $validated['compare_at_price'] ?? null,
            'stock' => $validated['stock'],
            'unit' => $validated['unit'],
            'description' => $validated['description'],
            'image' => $imagePath,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('seller.products')->with('success', 'Produk berhasil diperbarui.');
    }

    public function deleteProduct(int $id)
    {
        $store = $this->getStore();
        $product = Product::where('store_id', $store->id)->findOrFail($id);
        $product->delete();

        return back()->with('success', 'Produk berhasil dihapus.');
    }

    public function toggleProductStatus(int $id)
    {
        $store = $this->getStore();
        $product = Product::where('store_id', $store->id)->findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);

        $statusText = $product->is_active ? 'diaktifkan untuk dijual' : 'dinonaktifkan (disembunyikan dari katalog)';
        return back()->with('success', "Produk '{$product->name}' berhasil {$statusText}.");
    }

    // WALLET & WITHDRAWAL
    public function wallet()
    {
        $store = $this->getStore();
        $wallet = Wallet::firstOrCreate(['store_id' => $store->id]);
        $withdrawals = Withdrawal::where('store_id', $store->id)->latest()->get();

        return view('seller.wallet', compact('store', 'wallet', 'withdrawals'));
    }

    public function requestWithdrawal(Request $request)
    {
        $store = $this->getStore();
        $wallet = Wallet::where('store_id', $store->id)->firstOrFail();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:20000|max:' . $wallet->balance,
            'bank_name' => 'required|string|max:50',
            'account_number' => 'required|string|max:50',
            'account_holder' => 'required|string|max:100',
        ]);

        Withdrawal::create([
            'wallet_id' => $wallet->id,
            'store_id' => $store->id,
            'amount' => $validated['amount'],
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'account_holder' => $validated['account_holder'],
            'status' => 'pending',
        ]);

        $wallet->decrement('balance', $validated['amount']);

        return back()->with('success', 'Pengajuan pencairan dana Rp ' . number_format($validated['amount']) . ' berhasil diajukan!');
    }

    // REPORTS
    public function reports()
    {
        $store = $this->getStore();
        
        $dailySales = Order::where('store_id', $store->id)
            ->where('status', 'selesai')
            ->selectRaw('DATE(created_at) as date, SUM(seller_earnings) as total_earnings, COUNT(id) as total_orders')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take(14)
            ->get();

        $reviews = Review::with(['buyer', 'product'])
            ->where('store_id', $store->id)
            ->latest()
            ->get();

        return view('seller.reports', compact('store', 'dailySales', 'reviews'));
    }

    public function exportReportsCsv()
    {
        $store = $this->getStore();
        if (!$store) {
            abort(403);
        }

        $orders = Order::with(['buyer', 'items.product', 'payment'])
            ->where('store_id', $store->id)
            ->latest()
            ->get();

        $filename = 'laporan-penjualan-' . Str::slug($store->name) . '-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            // Header row
            fputcsv($file, [
                'Nomor Pesanan',
                'Tanggal',
                'Nama Pembeli',
                'Status Pesanan',
                'Metode Pembayaran',
                'Tipe Pengiriman',
                'Item Produk & Jumlah',
                'Subtotal (Rp)',
                'Ongkir (Rp)',
                'Biaya Admin Platform (Rp)',
                'Pendapatan Bersih Penjual (Rp)'
            ]);

            foreach ($orders as $order) {
                $itemList = $order->items->map(function ($item) {
                    return ($item->product->name ?? 'Item') . ' (x' . $item->quantity . ')';
                })->implode('; ');

                fputcsv($file, [
                    $order->order_number,
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->buyer->name ?? 'Pelanggan',
                    strtoupper(str_replace('_', ' ', $order->status)),
                    strtoupper($order->payment->payment_method ?? 'QRIS'),
                    $order->delivery_type === 'antar' ? 'Diantar ke Alamat' : 'Pickup di Toko',
                    $itemList,
                    $order->subtotal,
                    $order->shipping_cost,
                    $order->admin_fee,
                    $order->seller_earnings
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function replyReview(Request $request, int $id)
    {
        $store = $this->getStore();
        $review = Review::where('store_id', $store->id)->findOrFail($id);

        $validated = $request->validate([
            'seller_reply' => 'required|string|max:500',
        ]);

        $review->update([
            'seller_reply' => $validated['seller_reply'],
            'replied_at' => now(),
        ]);

        return back()->with('success', 'Balasan ulasan berhasil dikirim!');
    }

    // STORE SETTINGS (Supports File Upload Logo & Banner)
    public function settings()
    {
        $store = $this->getStore();
        $categories = Category::where('is_active', true)->get();
        return view('seller.settings', compact('store', 'categories'));
    }

    public function updateSettings(Request $request)
    {
        $store = $this->getStore();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string',
            'operational_hours' => 'required|string',
            'phone' => 'required|string',
            'is_open' => 'nullable|boolean',
            'logo_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'logo_url' => 'nullable|url',
            'banner_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'banner_url' => 'nullable|url',
        ]);

        $logoPath = $store->logo;
        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('stores', 'public');
            $logoPath = '/storage/' . $path;
        } elseif (!empty($validated['logo_url'])) {
            $logoPath = $validated['logo_url'];
        }

        $bannerPath = $store->banner;
        if ($request->hasFile('banner_file')) {
            $path = $request->file('banner_file')->store('stores', 'public');
            $bannerPath = '/storage/' . $path;
        } elseif (!empty($validated['banner_url'])) {
            $bannerPath = $validated['banner_url'];
        }

        $store->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'address' => $validated['address'],
            'operational_hours' => $validated['operational_hours'],
            'phone' => $validated['phone'],
            'is_open' => $request->boolean('is_open'),
            'logo' => $logoPath,
            'banner' => $bannerPath,
        ]);

        return back()->with('success', 'Pengaturan toko berhasil diperbarui.');
    }
}
