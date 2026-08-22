@extends('layouts.seller')

@section('title', 'Kelola Pesanan — ' . $store->name)

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-black text-2xl text-[#0B5A45]">Kelola Pesanan Toko</h1>
            <p class="text-xs text-gray-500">Update status pesanan secara real-time agar pembeli mendapat notifikasi.</p>
        </div>

        <!-- Filter Status Bar -->
        <div class="flex items-center gap-1.5 overflow-x-auto text-xs bg-white p-1.5 rounded-2xl border border-gray-100 shadow-xs">
            <a href="{{ route('seller.orders') }}" class="px-3 py-1.5 rounded-xl font-bold {{ empty($status) ? 'bg-[#0E9F6E] text-white' : 'text-gray-600 hover:bg-gray-100' }}">Semua</a>
            <a href="{{ route('seller.orders', ['status' => 'menunggu_konfirmasi']) }}" class="px-3 py-1.5 rounded-xl font-bold {{ $status === 'menunggu_konfirmasi' ? 'bg-[#F2A93B] text-[#1E2723]' : 'text-gray-600 hover:bg-gray-100' }}">Menunggu</a>
            <a href="{{ route('seller.orders', ['status' => 'diproses']) }}" class="px-3 py-1.5 rounded-xl font-bold {{ $status === 'diproses' ? 'bg-[#0E9F6E] text-white' : 'text-gray-600 hover:bg-gray-100' }}">Diproses</a>
            <a href="{{ route('seller.orders', ['status' => 'siap_diambil_dikirim']) }}" class="px-3 py-1.5 rounded-xl font-bold {{ $status === 'siap_diambil_dikirim' ? 'bg-[#0E9F6E] text-white' : 'text-gray-600 hover:bg-gray-100' }}">Siap Antar</a>
            <a href="{{ route('seller.orders', ['status' => 'selesai']) }}" class="px-3 py-1.5 rounded-xl font-bold {{ $status === 'selesai' ? 'bg-[#0E9F6E] text-white' : 'text-gray-600 hover:bg-gray-100' }}">Selesai</a>
        </div>
    </div>

    <!-- Orders List Cards -->
    <div class="space-y-4">
        @forelse($orders as $order)
            <div class="bg-white rounded-3xl border border-gray-100 p-5 sm:p-6 shadow-sm space-y-4 hover:border-[#0E9F6E] transition">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-xs font-bold text-[#0B5A45] bg-[#EDFDF5] px-2.5 py-1 rounded-lg">{{ $order->order_number }}</span>
                        <span class="text-xs text-gray-400">• {{ $order->created_at->format('d M Y, H:i') }}</span>
                        <span class="bg-gray-100 text-gray-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase">{{ $order->fulfillment_type }}</span>
                        @if($order->estimated_delivery_minutes)
                            <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                                <i data-lucide="timer" class="w-3 h-3"></i> SLA: ~{{ $order->estimated_delivery_minutes }} mnt
                            </span>
                        @endif
                    </div>

                    <x-status-pulse :status="$order->status" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Customer & Items Details -->
                    <div class="md:col-span-8 space-y-2 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-gray-900 text-sm">{{ $order->buyer->name }}</span>
                            <span class="text-gray-500 font-mono">({{ $order->buyer->phone }})</span>
                        </div>
                        
                        <div class="bg-[#FAF8F2] p-3 rounded-2xl border border-gray-100 space-y-1.5">
                            @foreach($order->items as $it)
                                <div class="flex justify-between">
                                    <span><b>{{ $it->quantity }}x</b> {{ $it->product_name }} @if($it->variant_name) <span class="text-gray-400">({{ $it->variant_name }})</span> @endif</span>
                                    <span class="font-mono font-bold text-gray-700">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                            @if($order->buyer_notes)
                                <p class="text-amber-800 italic pt-1 border-t border-gray-200">"Catatan: {{ $order->buyer_notes }}"</p>
                            @endif
                        </div>
                    </div>

                    <!-- Financial & Action State Handler -->
                    <div class="md:col-span-4 flex flex-col justify-between items-end gap-3 text-right">
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase font-bold block">Pendapatan Bersih Toko</span>
                            <span class="font-mono font-black text-lg text-[#0B5A45]">
                                Rp {{ number_format($order->seller_earnings, 0, ',', '.') }}
                            </span>
                            <span class="block text-[10px] text-gray-400">Komisi 5%: Rp {{ number_format($order->commission_fee, 0, ',', '.') }}</span>
                        </div>

                        <!-- Action Buttons by State -->
                        <div class="flex items-center gap-2">
                            <!-- Direct Chat Buyer Button -->
                            <a href="{{ route('chats.show', \App\Models\Chat::firstOrCreate(['buyer_id' => $order->buyer_id, 'store_id' => $order->store_id])->id) }}" class="p-2 rounded-xl bg-[#FAF8F2] hover:bg-emerald-50 text-[#0B5A45] border border-gray-200 transition" title="Chat Pembeli">
                                <i data-lucide="message-square" class="w-4 h-4"></i>
                            </a>

                            @if($order->status === 'menunggu_konfirmasi')
                                <form action="{{ route('seller.orders.status', $order->id) }}" method="POST" class="flex items-center gap-1.5">
                                    @csrf
                                    <input type="hidden" name="action" value="accept">
                                    <select name="estimated_minutes" class="text-xs bg-[#FAF8F2] border border-gray-200 rounded-xl px-2 py-1.5 focus:border-[#0E9F6E] focus:outline-none font-medium">
                                        <option value="15">~15 mnt</option>
                                        <option value="25" selected>~25 mnt</option>
                                        <option value="40">~40 mnt</option>
                                        <option value="60">~60 mnt</option>
                                    </select>
                                    <button type="submit" class="bg-[#0E9F6E] hover:bg-[#086644] text-white font-bold px-3.5 py-1.5 rounded-xl text-xs shadow-sm transition">
                                        Terima
                                    </button>
                                </form>
                                <form action="{{ route('seller.orders.status', $order->id) }}" method="POST" onsubmit="return confirm('Tolak pesanan?');">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 font-bold px-2.5 py-1.5 rounded-xl text-xs transition">
                                        Tolak
                                    </button>
                                </form>
                            @elseif($order->status === 'diproses')
                                <form action="{{ route('seller.orders.status', $order->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="ready">
                                    <button type="submit" class="bg-[#F2A93B] hover:bg-[#D88E23] text-[#1E2723] font-bold px-4 py-2 rounded-xl text-xs shadow-sm transition flex items-center gap-1.5">
                                        <i data-lucide="package-check" class="w-4 h-4"></i>
                                        <span>Siap Diantar / Diambil</span>
                                    </button>
                                </form>
                            @elseif($order->status === 'siap_diambil_dikirim')
                                <form action="{{ route('seller.orders.status', $order->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="complete">
                                    <button type="submit" class="bg-[#0E9F6E] hover:bg-[#086644] text-white font-bold px-4 py-2 rounded-xl text-xs shadow-sm transition flex items-center gap-1.5">
                                        <i data-lucide="check-check" class="w-4 h-4"></i>
                                        <span>Selesaikan Pesanan</span>
                                    </button>
                                </form>
                            @endif
                        </div>

                    </div>
                </div>

            </div>
        @empty
            <div class="bg-white p-12 text-center rounded-3xl border border-gray-100 text-gray-400">
                Tidak ada pesanan dengan filter ini.
            </div>
        @endforelse

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>

</div>
@endsection
