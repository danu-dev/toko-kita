<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use App\Models\Order;
use App\Models\Category;
use App\Models\Withdrawal;
use App\Models\Dispute;
use App\Models\Banner;
use App\Models\PlatformSetting;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function dashboard()
    {
        $gmv = Order::where('status', 'selesai')->sum('total');
        $platformRevenue = Order::where('status', 'selesai')->sum('commission_fee');
        $totalOrders = Order::count();
        $totalStores = Store::count();
        $pendingStoresCount = Store::where('status', 'pending')->count();
        $pendingWithdrawalsCount = Withdrawal::where('status', 'pending')->count();
        $openDisputesCount = Dispute::whereIn('status', ['opened', 'seller_response', 'admin_review'])->count();

        // Recent platform transactions
        $recentOrders = Order::with(['buyer', 'store', 'payment'])->latest()->take(6)->get();

        // Pending verifications
        $pendingStores = Store::with('user', 'category')->where('status', 'pending')->take(5)->get();

        return view('admin.dashboard', compact(
            'gmv', 'platformRevenue', 'totalOrders', 'totalStores',
            'pendingStoresCount', 'pendingWithdrawalsCount', 'openDisputesCount',
            'recentOrders', 'pendingStores'
        ));
    }

    // MITRA VERIFICATION
    public function verifications()
    {
        $stores = Store::with('user', 'category')->latest()->paginate(15);
        return view('admin.verifications', compact('stores'));
    }

    public function processVerification(Request $request, int $id)
    {
        $store = Store::findOrFail($id);
        $action = $request->input('action'); // approve, reject
        $reason = $request->input('rejection_reason');

        if ($action === 'approve') {
            $store->update([
                'status' => 'approved',
                'rejection_reason' => null,
            ]);
            return back()->with('success', "Mitra Toko '{$store->name}' berhasil DISETUJUI.");
        } elseif ($action === 'reject') {
            $store->update([
                'status' => 'rejected',
                'rejection_reason' => $reason ?: 'Dokumen/data pendaftaran belum memenuhi syarat.',
            ]);
            return back()->with('info', "Mitra Toko '{$store->name}' telah DITOLAK.");
        }

        return back();
    }

    // CATEGORIES
    public function categories()
    {
        $categories = Category::withCount(['products', 'stores'])->latest()->get();
        return view('admin.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string',
            'image_file' => 'nullable|image|max:2048',
            'image_url' => 'nullable|url',
        ]);

        $imagePath = $validated['image_url'] ?? null;
        if ($request->hasFile('image_file')) {
            $imagePath = '/storage/' . $request->file('image_file')->store('categories', 'public');
        }

        Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'icon' => $validated['icon'] ?? 'tag',
            'image' => $imagePath,
            'is_active' => true,
        ]);

        return back()->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    public function deleteCategory(int $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return back()->with('success', 'Kategori dihapus.');
    }

    // TRANSACTIONS MONITORING
    public function transactions(Request $request)
    {
        $status = $request->input('status');
        $orders = Order::with(['buyer', 'store', 'payment', 'statusHistories']);

        if ($status) {
            $orders->where('status', $status);
        }

        $orders = $orders->latest()->paginate(15)->withQueryString();

        return view('admin.transactions', compact('orders', 'status'));
    }

    // DISPUTE RESOLUTION
    public function disputes()
    {
        $disputes = Dispute::with(['order.items', 'buyer', 'store', 'resolver'])->latest()->paginate(10);
        return view('admin.disputes', compact('disputes'));
    }

    public function resolveDispute(Request $request, int $id)
    {
        $dispute = Dispute::findOrFail($id);
        $decision = $request->input('decision'); // refund, reject_dispute
        $adminNotes = $request->input('admin_decision');

        if ($decision === 'refund') {
            $dispute->update([
                'status' => 'resolved_refund',
                'admin_decision' => $adminNotes ?: 'Pengembalian dana (refund) disetujui admin.',
                'resolved_by' => Auth::id(),
                'resolved_at' => now(),
            ]);

            // Transition order to retur_refund
            $this->orderService->transition($dispute->order, OrderService::STATUS_RETUR, Auth::user(), 'Dispute diselesaikan dengan Refund.');

            return back()->with('success', 'Dispute diselesaikan: Pesanan di-refund ke pembeli.');
        } elseif ($decision === 'reject_dispute') {
            $dispute->update([
                'status' => 'resolved_rejected',
                'admin_decision' => $adminNotes ?: 'Komplain ditolak admin setelah peninjauan bukti.',
                'resolved_by' => Auth::id(),
                'resolved_at' => now(),
            ]);

            return back()->with('info', 'Dispute ditolak: Dana tetap diteruskan ke penjual.');
        }

        return back();
    }

    // WITHDRAWALS APPROVAL
    public function withdrawals()
    {
        $withdrawals = Withdrawal::with(['store', 'wallet'])->latest()->paginate(15);
        return view('admin.withdrawals', compact('withdrawals'));
    }

    public function processWithdrawal(Request $request, int $id)
    {
        $withdrawal = Withdrawal::findOrFail($id);
        $action = $request->input('action'); // approve, reject
        $notes = $request->input('admin_notes');

        if ($action === 'approve') {
            $withdrawal->update([
                'status' => 'approved',
                'admin_notes' => $notes ?: 'Pencairan dana telah ditransfer ke rekening bank mitra.',
                'processed_at' => now(),
            ]);
            return back()->with('success', 'Pencairan dana berhasil disetujui!');
        } elseif ($action === 'reject') {
            $withdrawal->update([
                'status' => 'rejected',
                'admin_notes' => $notes ?: 'Pencairan ditolak. Dana dikembalikan ke saldo dompet.',
                'processed_at' => now(),
            ]);
            // Refund back to store wallet
            $withdrawal->wallet->increment('balance', $withdrawal->amount);
            return back()->with('info', 'Pencairan dana ditolak & saldo dikembalikan ke toko.');
        }

        return back();
    }

    // PLATFORM SETTINGS & BANNERS (File Upload OR URL)
    public function settings()
    {
        $commissionPercent = PlatformSetting::get('platform_commission_percent', 5);
        $banners = Banner::orderBy('order_position')->get();
        return view('admin.settings', compact('commissionPercent', 'banners'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'platform_commission_percent' => 'required|numeric|min:0|max:50',
        ]);

        PlatformSetting::set('platform_commission_percent', $validated['platform_commission_percent']);

        return back()->with('success', 'Pengaturan komisi platform berhasil disimpan.');
    }

    public function storeBanner(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'banner_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:4096',
            'banner_url' => 'nullable|url',
            'badge_text' => 'nullable|string|max:50',
            'link' => 'nullable|string|max:255',
        ]);

        if (!$request->hasFile('banner_file') && empty($validated['banner_url'])) {
            return back()->with('error', 'Silakan pilih gambar file lokal atau masukkan URL gambar banner!');
        }

        $imageUrl = $validated['banner_url'] ?? null;
        if ($request->hasFile('banner_file')) {
            $path = $request->file('banner_file')->store('banners', 'public');
            $imageUrl = '/storage/' . $path;
        }

        Banner::create([
            'title' => $validated['title'],
            'image' => $imageUrl,
            'badge_text' => $validated['badge_text'],
            'link' => $validated['link'] ?? '/jelajah',
            'order_position' => Banner::count() + 1,
            'is_active' => true,
        ]);

        return back()->with('success', 'Banner promosi baru berhasil dipasang!');
    }

    public function deleteBanner(int $id)
    {
        $banner = Banner::findOrFail($id);
        if (str_starts_with($banner->image, '/storage/')) {
            $relativePath = str_replace('/storage/', '', $banner->image);
            Storage::disk('public')->delete($relativePath);
        }
        $banner->delete();
        return back()->with('success', 'Banner berhasil dihapus.');
    }
}
