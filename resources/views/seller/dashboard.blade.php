@extends('layouts.seller')

@section('title', 'Dashboard Toko — ' . $store->name)

@section('content')
<div class="space-y-6">

    <!-- Store Status Banner if Pending/Rejected -->
    @if($store->status === 'pending')
        <div class="bg-[#FEF9EE] border border-[#F2A93B]/40 p-5 rounded-3xl flex items-start gap-4">
            <div class="p-2.5 bg-[#F2A93B] text-[#1E2723] rounded-2xl shrink-0">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="font-display font-bold text-sm text-amber-950">Pendaftaran Toko Sedang Ditinjau Admin</h3>
                <p class="text-xs text-amber-900/80 mt-1 leading-relaxed">
                    Toko Anda saat ini dalam antrean verifikasi tim Toko Kita. Anda sudah bisa mulai menambahkan produk dan mengatur profil toko sambil menunggu approval.
                </p>
            </div>
        </div>
    @endif

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Metric 1: Total Revenue -->
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-xs font-bold uppercase tracking-wider">Total Pendapatan Bersih</span>
                <div class="p-2 rounded-xl bg-emerald-50 text-[#0E9F6E]">
                    <i data-lucide="banknote" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="font-mono font-black text-2xl text-[#0B5A45]">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-gray-400">Sudah dipotong komisi 5%</span>
        </div>

        <!-- Metric 2: Available Wallet Balance -->
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-xs font-bold uppercase tracking-wider">Saldo Siap Tarik</span>
                <div class="p-2 rounded-xl bg-amber-50 text-[#F2A93B]">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="font-mono font-black text-2xl text-[#F2A93B]">
                Rp {{ number_format($wallet->balance, 0, ',', '.') }}
            </div>
            <a href="{{ route('seller.wallet') }}" class="text-[11px] font-bold text-[#0E9F6E] hover:underline block">Tarik Dana &rarr;</a>
        </div>

        <!-- Metric 3: Active Orders -->
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-xs font-bold uppercase tracking-wider">Pesanan Berjalan</span>
                <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="font-mono font-black text-2xl text-blue-700">
                {{ $activeOrders }}
            </div>
            <span class="text-[11px] text-gray-400">Perlu diproses / dikirim</span>
        </div>

        <!-- Metric 4: Rating -->
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-gray-400">
                <span class="text-xs font-bold uppercase tracking-wider">Rating & Ulasan</span>
                <div class="p-2 rounded-xl bg-yellow-50 text-yellow-500">
                    <i data-lucide="star" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="font-mono font-black text-2xl text-gray-800 flex items-center gap-1.5">
                <span>{{ number_format($store->rating, 1) }}</span>
                <span class="text-xs text-gray-400 font-sans font-normal">/ 5.0 ({{ $store->total_reviews }})</span>
            </div>
            <span class="text-[11px] text-emerald-600 font-semibold">Toko Sangat Baik</span>
        </div>

    </div>

    <!-- Live Incoming Orders Alert Box (PRD Section 3.2 - Real-time incoming order sound/action) -->
    @if($incomingOrders->isNotEmpty())
        <div class="bg-[#FEF9EE] p-6 rounded-3xl border-2 border-[#F2A93B] shadow-lg space-y-4 animate-pulse">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="p-2 bg-[#F2A93B] text-[#1E2723] rounded-2xl">
                        <i data-lucide="bell-ring" class="w-6 h-6 animate-bounce"></i>
                    </span>
                    <div>
                        <h3 class="font-display font-black text-base text-amber-950">PESANAN BARU MASUK! (Perlu Konfirmasi)</h3>
                        <p class="text-xs text-amber-900/80">Segera terima atau tolak pesanan sebelum batas waktu habis.</p>
                    </div>
                </div>
                <span class="font-mono font-bold bg-[#F2A93B] text-[#1E2723] px-3 py-1 rounded-full text-xs">
                    {{ $incomingOrders->count() }} Menunggu
                </span>
            </div>

            <div class="divide-y divide-amber-200/60">
                @foreach($incomingOrders as $inc)
                    <div class="py-4 first:pt-2 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-1 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-gray-900">{{ $inc->order_number }}</span>
                                <span class="text-gray-400">• {{ $inc->created_at->diffForHumans() }}</span>
                                <span class="bg-emerald-100 text-[#0E9F6E] px-2 py-0.5 rounded font-bold uppercase text-[10px]">{{ $inc->fulfillment_type }}</span>
                            </div>
                            <p class="font-bold text-gray-800 text-sm">
                                {{ $inc->buyer->name }} ({{ $inc->buyer->phone }})
                            </p>
                            <p class="text-gray-600">
                                @foreach($inc->items as $it)
                                    {{ $it->quantity }}x {{ $it->product_name }}@if(!$loop->last), @endif
                                @endforeach
                            </p>
                            @if($inc->buyer_notes)
                                <p class="text-amber-800 italic">"{{ $inc->buyer_notes }}"</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="text-right mr-2 hidden sm:block">
                                <span class="text-[10px] text-gray-400 uppercase font-bold block">Pendapatan Toko</span>
                                <span class="font-mono font-bold text-base text-[#0B5A45]">Rp {{ number_format($inc->seller_earnings, 0, ',', '.') }}</span>
                            </div>

                            <!-- Accept Button -->
                            <form action="{{ route('seller.orders.status', $inc->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="accept">
                                <button type="submit" class="bg-[#0E9F6E] hover:bg-[#086644] text-white px-5 py-2.5 rounded-2xl text-xs font-bold shadow-md shadow-[#0E9F6E]/20 transition flex items-center gap-1.5">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span>Terima Pesanan</span>
                                </button>
                            </form>

                            <!-- Reject Button -->
                            <form action="{{ route('seller.orders.status', $inc->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menolak pesanan ini?');">
                                @csrf
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="reason" value="Stok habis / Toko sedang sangat ramai.">
                                <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2.5 rounded-2xl text-xs font-bold transition">
                                    Tolak
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Orders Table -->
    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-display font-bold text-base text-[#0B5A45]">Transaksi Terbaru Toko</h3>
            <a href="{{ route('seller.orders') }}" class="text-xs font-bold text-[#0E9F6E] hover:underline">Semua Pesanan &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-gray-400 border-b border-gray-100">
                        <th class="pb-3 font-semibold">NO. ORDER</th>
                        <th class="pb-3 font-semibold">PEMBELI</th>
                        <th class="pb-3 font-semibold">TOTAL PENDAPATAN</th>
                        <th class="pb-3 font-semibold">STATUS</th>
                        <th class="pb-3 font-semibold text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-[#FAF8F2]/60 transition">
                            <td class="py-3.5 font-mono font-bold text-gray-800">
                                {{ $order->order_number }}
                                <span class="block text-[10px] text-gray-400 font-sans font-normal">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="py-3.5">
                                <span class="font-bold text-gray-800">{{ $order->buyer->name }}</span>
                                <span class="block text-[10px] text-gray-400">{{ $order->items->count() }} item</span>
                            </td>
                            <td class="py-3.5 font-mono font-bold text-[#0B5A45]">
                                Rp {{ number_format($order->seller_earnings, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5">
                                <x-status-pulse :status="$order->status" />
                            </td>
                            <td class="py-3.5 text-right">
                                <a href="{{ route('seller.orders') }}" class="bg-[#FAF8F2] hover:bg-[#0E9F6E] text-[#0B5A45] hover:text-white px-3 py-1.5 rounded-xl font-bold transition">
                                    Kelola
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-400">Belum ada transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
