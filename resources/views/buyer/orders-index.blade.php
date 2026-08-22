@extends('layouts.app')

@section('title', 'Riwayat Pesanan Saya — Toko Kita')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display font-black text-2xl text-[#0B5A45]">Pesanan Saya</h1>
            <p class="text-xs text-gray-500">Pantau status transaksi dan lacak pesananmu secara langsung.</p>
        </div>
    </div>

    <!-- Order Status Tabs Filter Bar -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
        <a href="{{ route('orders.index') }}" class="px-4 py-2 rounded-2xl whitespace-nowrap font-bold transition {{ empty($status) ? 'bg-[#0E9F6E] text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
            Semua
        </a>
        <a href="{{ route('orders.index', ['status' => 'menunggu_konfirmasi']) }}" class="px-4 py-2 rounded-2xl whitespace-nowrap font-bold transition {{ $status === 'menunggu_konfirmasi' ? 'bg-[#0E9F6E] text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
            Menunggu
        </a>
        <a href="{{ route('orders.index', ['status' => 'diproses']) }}" class="px-4 py-2 rounded-2xl whitespace-nowrap font-bold transition {{ $status === 'diproses' ? 'bg-[#0E9F6E] text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
            Diproses
        </a>
        <a href="{{ route('orders.index', ['status' => 'siap_diambil_dikirim']) }}" class="px-4 py-2 rounded-2xl whitespace-nowrap font-bold transition {{ $status === 'siap_diambil_dikirim' ? 'bg-[#0E9F6E] text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
            Siap Dikirim
        </a>
        <a href="{{ route('orders.index', ['status' => 'selesai']) }}" class="px-4 py-2 rounded-2xl whitespace-nowrap font-bold transition {{ $status === 'selesai' ? 'bg-[#0E9F6E] text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
            Selesai
        </a>
        <a href="{{ route('orders.index', ['status' => 'dibatalkan']) }}" class="px-4 py-2 rounded-2xl whitespace-nowrap font-bold transition {{ $status === 'dibatalkan' ? 'bg-[#0E9F6E] text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
            Dibatalkan
        </a>
    </div>

    @if($orders->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-gray-100 shadow-sm space-y-4">
            <div class="w-16 h-16 rounded-3xl bg-[#FAF8F2] text-gray-400 mx-auto flex items-center justify-center">
                <i data-lucide="receipt" class="w-8 h-8"></i>
            </div>
            <div>
                <h3 class="font-display font-bold text-lg text-gray-800">Belum Ada Riwayat Pesanan</h3>
                <p class="text-xs text-gray-500 mt-1">Pesanan yang kamu buat akan muncul di sini.</p>
            </div>
            <a href="{{ route('explore') }}" class="inline-block bg-[#0E9F6E] hover:bg-[#086644] text-white font-bold px-6 py-3 rounded-2xl text-xs shadow-md transition">
                Jelajahi Produk Sekarang
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 space-y-4 hover:border-[#0E9F6E] transition">
                    
                    <!-- Order Card Top Bar -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-xs font-bold text-[#0B5A45] bg-[#EDFDF5] px-2.5 py-1 rounded-lg">
                                {{ $order->order_number }}
                            </span>
                            <span class="text-xs text-gray-400">• {{ $order->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        
                        <!-- Status Pulse Badge (Design Signature Element) -->
                        <x-status-pulse :status="$order->status" />
                    </div>

                    <!-- Store & Item Summary -->
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold text-gray-800 flex items-center gap-1.5">
                                <i data-lucide="store" class="w-3.5 h-3.5 text-[#0E9F6E]"></i>
                                <span>{{ $order->store->name }}</span>
                            </p>
                            <p class="text-xs text-gray-600 mt-1">
                                {{ $order->items->first()?->product_name }}
                                @if($order->items->count() > 1)
                                    <span class="text-gray-400 font-semibold">+{{ $order->items->count() - 1 }} produk lainnya</span>
                                @endif
                            </p>
                        </div>

                        <div class="text-right">
                            <span class="text-[10px] text-gray-400 uppercase font-bold block">Total Tagihan</span>
                            <span class="font-mono font-bold text-base text-[#0B5A45]">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <!-- Bottom Actions Bar -->
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-xs">
                        <div class="text-[11px] text-gray-500">
                            Metode: <b class="uppercase">{{ $order->payment?->method ?? 'QRIS' }}</b> ({{ $order->fulfillment_type === 'delivery' ? 'Antar' : 'Pickup' }})
                        </div>

                        <a href="{{ route('orders.track', $order->id) }}" class="bg-[#FAF8F2] hover:bg-[#0E9F6E] text-[#0B5A45] hover:text-white font-bold px-4 py-2 rounded-xl flex items-center gap-1.5 transition">
                            <span>Lacak & Detail</span>
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>

                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif

</div>
@endsection
