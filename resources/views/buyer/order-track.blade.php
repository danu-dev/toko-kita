@extends('layouts.app')

@section('title', 'Lacak Pesanan #' . $order->order_number . ' — Toko Kita')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Tracking -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('orders.index') }}" class="p-2 rounded-full bg-white border border-gray-200 hover:bg-gray-50 transition">
                <i data-lucide="arrow-left" class="w-4 h-4 text-gray-700"></i>
            </a>
            <div>
                <span class="font-mono text-xs font-bold text-[#0B5A45]">{{ $order->order_number }}</span>
                <h1 class="font-display font-black text-xl sm:text-2xl text-[#1E2723]">Lacak Pesanan</h1>
            </div>
        </div>

        <x-status-pulse :status="$order->status" />
    </div>

    <!-- Status Pulse Card & Progress Track (Gojek-like State Visualizer) -->
    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-6">
        
        <!-- Live Visual Bar (4 Core Steps) -->
        @php
            $steps = [
                'menunggu_konfirmasi' => ['label' => 'Menunggu', 'icon' => 'clock'],
                'diproses' => ['label' => 'Diproses', 'icon' => 'utensils'],
                'siap_diambil_dikirim' => ['label' => 'Siap Kirim', 'icon' => 'truck'],
                'selesai' => ['label' => 'Selesai', 'icon' => 'check-circle'],
            ];

            $orderRank = match($order->status) {
                'menunggu_konfirmasi' => 1,
                'diproses' => 2,
                'siap_diambil_dikirim' => 3,
                'selesai' => 4,
                default => 0,
            };
        @endphp

        @if($order->status === 'dibatalkan')
            <div class="bg-red-50 p-4 rounded-2xl border border-red-200 flex items-center gap-3 text-red-700 text-xs">
                <i data-lucide="x-circle" class="w-5 h-5 shrink-0"></i>
                <div>
                    <span class="font-bold block">Pesanan Telah Dibatalkan</span>
                    <p class="text-red-600 mt-0.5">Alasan: {{ $order->cancellation_reason ?: 'Tidak ada keterangan spesifik.' }}</p>
                </div>
            </div>
        @elseif($order->status === 'retur_refund')
            <div class="bg-purple-50 p-4 rounded-2xl border border-purple-200 flex items-center gap-3 text-purple-700 text-xs">
                <i data-lucide="rotate-ccw" class="w-5 h-5 shrink-0"></i>
                <div>
                    <span class="font-bold block">Pesanan Telah Selesai dengan Refund</span>
                    <p class="text-purple-600 mt-0.5">Komplain disetujui admin dan dana dikembalikan.</p>
                </div>
            </div>
        @else
            <!-- Visual Stepper -->
            <div class="grid grid-cols-4 gap-2 relative">
                @php $stepIdx = 1; @endphp
                @foreach($steps as $key => $s)
                    @php 
                        $isCurrent = ($stepIdx === $orderRank);
                        $isDone = ($stepIdx <= $orderRank);
                    @endphp
                    <div class="flex flex-col items-center text-center relative z-10">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center mb-1.5 transition-all shadow-xs {{ $isCurrent ? 'bg-[#0E9F6E] text-white scale-110 pulse-green' : ($isDone ? 'bg-[#0B5A45] text-white' : 'bg-gray-100 text-gray-400') }}">
                            <i data-lucide="{{ $s['icon'] }}" class="w-5 h-5"></i>
                        </div>
                        <span class="text-[11px] font-bold {{ $isDone ? 'text-[#0B5A45]' : 'text-gray-400' }}">{{ $s['label'] }}</span>
                    </div>
                    @php $stepIdx++; @endphp
                @endforeach
            </div>

            @if($order->estimated_delivery_minutes && in_array($order->status, ['diproses', 'siap_diambil_dikirim']))
                <div class="bg-[#EDFDF5] border border-emerald-200 rounded-2xl p-3.5 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-[#0E9F6E] text-white flex items-center justify-center">
                            <i data-lucide="timer" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="font-bold text-[#0B5A45] block">Estimasi Waktu Tiba (SLA)</span>
                            <p class="text-gray-600 text-[11px]">Penjual memperkirakan pesanan selesai ~{{ $order->estimated_delivery_minutes }} menit</p>
                        </div>
                    </div>
                    @if($order->estimated_delivery_at)
                        <div class="text-right font-mono">
                            <span class="text-[10px] text-gray-400 block">Target:</span>
                            <span class="font-bold text-[#0B5A45] text-sm">{{ $order->estimated_delivery_at->format('H:i') }} WIB</span>
                        </div>
                    @endif
                </div>
            @endif
        @endif

        <!-- Audit Trail / History Logs (PRD Section 3.4) -->
        <div class="border-t border-gray-100 pt-5 space-y-3">
            <h3 class="font-display font-bold text-xs text-[#0B5A45] uppercase tracking-wider">Aktivitas & Riwayat Status</h3>
            <div class="space-y-3 pl-2 border-l-2 border-emerald-100 ml-3">
                @foreach($order->statusHistories as $history)
                    <div class="relative pl-4 text-xs">
                        <span class="absolute -left-[1.35rem] top-1 w-2.5 h-2.5 rounded-full bg-[#0E9F6E] ring-4 ring-white"></span>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-gray-800 uppercase tracking-wide text-[11px]">{{ str_replace('_', ' ', $history->to_status) }}</span>
                            <span class="text-[10px] text-gray-400 font-mono">{{ $history->created_at->format('H:i, d M Y') }}</span>
                        </div>
                        <p class="text-gray-600 mt-0.5">{{ $history->notes }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Buyer Action Buttons (Cancel / Complete / Chat) -->
        <div class="border-t border-gray-100 pt-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <a href="{{ route('chats.show', \App\Models\Chat::firstOrCreate(['buyer_id' => $order->buyer_id, 'store_id' => $order->store_id])->id) }}" class="bg-[#FAF8F2] hover:bg-emerald-50 text-[#0B5A45] px-4 py-2 rounded-xl text-xs font-bold border border-gray-200 flex items-center gap-1.5 transition">
                    <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                    <span>Chat Toko</span>
                </a>
            </div>

            <div class="flex items-center gap-2">
                @if($order->status === 'menunggu_konfirmasi')
                    <form action="{{ route('orders.cancel', $order->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');">
                        @csrf
                        <input type="hidden" name="reason" value="Dibatalkan atas permintaan pembeli sebelum diproses.">
                        <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-xl text-xs font-bold transition">
                            Batalkan Pesanan
                        </button>
                    </form>
                @endif

                @if($order->status === 'siap_diambil_dikirim')
                    <form action="{{ route('orders.complete', $order->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-[#0E9F6E] hover:bg-[#086644] text-white px-5 py-2 rounded-xl text-xs font-bold shadow-md shadow-[#0E9F6E]/20 transition flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Pesanan Diterima (Selesai)</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

    </div>

    <!-- Rating & Review Section (if completed) -->
    @if($order->status === 'selesai')
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-display font-bold text-base text-[#0B5A45]">Rating & Ulasan Mitra</h3>
                <span class="text-xs text-[#F2A93B] font-bold">+50 Poin Loyalitas</span>
            </div>

            @if($order->review)
                <div class="p-4 bg-[#FAF8F2] rounded-2xl space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1 text-[#F2A93B]">
                            @for($i=1; $i<=5; $i++)
                                <i data-lucide="star" class="w-3.5 h-3.5 {{ $i <= $order->review->rating ? 'fill-current' : 'text-gray-200' }}"></i>
                            @endfor
                        </div>
                        <span class="text-[10px] text-gray-400 font-mono">{{ $order->review->created_at->format('d M Y') }}</span>
                    </div>
                    <p class="text-gray-700">{{ $order->review->comment }}</p>

                    @if($order->review->seller_reply)
                        <div class="bg-white p-3 rounded-xl border border-emerald-100 text-xs mt-2">
                            <span class="font-bold text-[#0E9F6E] block mb-0.5">Balasan Toko:</span>
                            <p class="text-gray-600">{{ $order->review->seller_reply }}</p>
                        </div>
                    @endif
                </div>
            @else
                <form action="{{ route('orders.review', $order->id) }}" method="POST" class="space-y-3" x-data="{ rating: 5 }">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Berikan Bintang:</label>
                        <div class="flex items-center gap-2">
                            <template x-for="star in [1, 2, 3, 4, 5]">
                                <button type="button" @click="rating = star" class="p-1 text-gray-300 hover:text-[#F2A93B] transition">
                                    <i data-lucide="star" class="w-6 h-6" :class="star <= rating ? 'text-[#F2A93B] fill-[#F2A93B]' : 'text-gray-300'"></i>
                                </button>
                            </template>
                            <input type="hidden" name="rating" :value="rating">
                        </div>
                    </div>
                    <div>
                        <textarea name="comment" rows="2" required placeholder="Tuliskan ulasan makanan / kecepatan pelayanan..." class="w-full px-3.5 py-2 bg-[#FAF8F2] border border-gray-200 rounded-xl text-xs focus:border-[#0E9F6E]"></textarea>
                    </div>
                    <button type="submit" class="bg-[#F2A93B] hover:bg-[#D88E23] text-[#1E2723] px-5 py-2 rounded-xl font-bold text-xs shadow-sm transition">
                        Kirim Ulasan
                    </button>
                </form>
            @endif

            <!-- 24H Dispute Option -->
            @if(!$order->dispute)
                <div class="pt-3 border-t border-gray-100 text-xs flex items-center justify-between text-gray-500" x-data="{ openDispute: false }">
                    <span>Ada masalah dengan pesanan?</span>
                    <button @click="openDispute = !openDispute" class="text-red-500 hover:underline font-semibold">Ajukan Komplain / Dispute</button>

                    <div x-show="openDispute" x-cloak class="w-full mt-3 bg-red-50/50 p-4 rounded-2xl border border-red-200">
                        <form action="{{ route('orders.dispute', $order->id) }}" method="POST" class="space-y-2">
                            @csrf
                            <input type="text" name="reason" required placeholder="Judul masalah (misal: pesanan kurang / rusak)" class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs">
                            <textarea name="description" rows="2" required placeholder="Jelaskan detail keluhan..." class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs"></textarea>
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-1.5 rounded-xl text-xs">Kirim Pengaduan</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="p-3 bg-purple-50 rounded-2xl border border-purple-200 text-xs text-purple-800">
                    <b>Status Komplain:</b> {{ $order->dispute->status }} — {{ $order->dispute->reason }}
                </div>
            @endif
        </div>
    @endif

    <!-- Order Items & Invoice Details -->
    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-display font-bold text-base text-[#0B5A45]">Rincian Transaksi & Struk Digital</h3>
            <a href="{{ route('orders.invoice', $order->id) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0B5A45] hover:text-[#0E9F6E] bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-xl border border-emerald-200 transition">
                <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                <span>Lihat Struk / Invoice</span>
            </a>
        </div>

        <div class="divide-y divide-gray-100 text-xs">
            @foreach($order->items as $item)
                <div class="py-2.5 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-gray-800">{{ $item->quantity }}x {{ $item->product_name }}</span>
                        @if($item->variant_name)
                            <span class="text-gray-400">({{ $item->variant_name }})</span>
                        @endif
                    </div>
                    <span class="font-mono font-bold text-gray-700">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>

        <div class="bg-[#FAF8F2] p-4 rounded-2xl space-y-1.5 text-xs">
            <div class="flex justify-between text-gray-600">
                <span>Subtotal</span>
                <span class="font-mono font-bold">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
                <span>Ongkos Kirim ({{ $order->fulfillment_type }})</span>
                <span class="font-mono font-bold">Rp {{ number_format($order->delivery_fee, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
                <span>Biaya Layanan</span>
                <span class="font-mono font-bold">Rp {{ number_format($order->service_fee, 0, ',', '.') }}</span>
            </div>
            <div class="pt-2 border-t border-gray-200 flex justify-between items-baseline">
                <span class="font-bold text-sm text-[#1E2723]">Total Pembayaran</span>
                <span class="font-mono font-black text-base text-[#0B5A45]">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

</div>
@endsection
