@extends('layouts.app')

@section('title', 'Invoice Struk Digital #' . $order->order_number . ' — Toko Kita')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Top Action Bar -->
    <div class="flex items-center justify-between print:hidden">
        <div class="flex items-center gap-3">
            <a href="{{ route('orders.track', $order->id) }}" class="p-2 rounded-full bg-white border border-gray-200 hover:bg-gray-50 transition">
                <i data-lucide="arrow-left" class="w-4 h-4 text-gray-700"></i>
            </a>
            <div>
                <span class="font-mono text-xs font-bold text-[#0B5A45]">{{ $order->order_number }}</span>
                <h1 class="font-display font-black text-xl text-[#1E2723]">Struk Digital & Invoice</h1>
            </div>
        </div>

        <button onclick="window.print()" class="bg-[#0B5A45] hover:bg-[#084233] text-white px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm transition">
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>Cetak / Simpan PDF</span>
        </button>
    </div>

    <!-- Official Invoice Receipt Card -->
    <div class="bg-white p-8 rounded-3xl border border-gray-200 shadow-sm space-y-6 print:border-none print:shadow-none print:p-0">
        
        <!-- Header Branding & Meta -->
        <div class="flex justify-between items-start border-b border-gray-100 pb-6">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-[#0B5A45] flex items-center justify-center text-white font-black text-xs">TK</div>
                    <span class="font-display font-extrabold text-lg text-[#0B5A45]">Toko<span class="text-[#F2A93B]">Kita</span></span>
                </div>
                <p class="text-[11px] text-gray-500">Platform Pasar & Kuliner Hyperlocal Indonesia</p>
            </div>

            <div class="text-right">
                <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider font-mono {{ $order->payment?->status === 'berhasil' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                    {{ $order->payment?->status === 'berhasil' ? 'LUNAS (PAID)' : strtoupper($order->status) }}
                </span>
                <p class="text-xs font-mono font-bold text-gray-800 mt-1.5">{{ $order->order_number }}</p>
                <p class="text-[10px] text-gray-400 font-mono">{{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
            </div>
        </div>

        <!-- Store & Buyer Metadata Grid -->
        <div class="grid grid-cols-2 gap-6 text-xs border-b border-gray-100 pb-6">
            <div>
                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block mb-1">Diterbitkan Oleh (Mitra / Penjual):</span>
                <h3 class="font-bold text-gray-900 text-sm">{{ $order->store->name }}</h3>
                <p class="text-gray-600 mt-0.5">{{ $order->store->address ?? 'Area Layanan Terdaftar' }}</p>
                <p class="text-gray-500 font-mono text-[11px] mt-0.5">Telepon: {{ $order->store->phone ?? '-' }}</p>
            </div>

            <div>
                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block mb-1">Ditujukan Kepada (Pembeli):</span>
                <h3 class="font-bold text-gray-900 text-sm">{{ $order->buyer->name }}</h3>
                <p class="text-gray-600 mt-0.5">
                    @if($order->address)
                        {{ $order->address->address_line }}, {{ $order->address->subdistrict }}, {{ $order->address->city }}
                    @else
                        Metode: Ambil Sendiri (Self-Pickup)
                    @endif
                </p>
                <p class="text-gray-500 font-mono text-[11px] mt-0.5">Metode Bayar: {{ strtoupper(str_replace('_', ' ', $order->payment?->payment_method ?? 'COD / Saldo')) }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="space-y-3">
            <h4 class="font-bold text-xs uppercase text-gray-400 tracking-wider">Daftar Produk / Menu</h4>
            
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 uppercase text-[10px] text-left">
                            <th class="py-2 font-bold">Produk</th>
                            <th class="py-2 text-center font-bold">Qty</th>
                            <th class="py-2 text-right font-bold">Harga Satuan</th>
                            <th class="py-2 text-right font-bold">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="py-3 pr-2">
                                    <span class="font-bold text-gray-800">{{ $item->product_name }}</span>
                                    @if($item->variant_name)
                                        <span class="text-gray-400 block text-[11px]">Varian: {{ $item->variant_name }}</span>
                                    @endif
                                    @if($item->notes)
                                        <span class="text-amber-700 block text-[10px] italic">Catatan: {{ $item->notes }}</span>
                                    @endif
                                </td>
                                <td class="py-3 text-center text-gray-600 font-mono">{{ $item->quantity }}</td>
                                <td class="py-3 text-right text-gray-600 font-mono">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="py-3 text-right font-bold text-gray-800 font-mono">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="border-t border-gray-100 pt-4 space-y-2 text-xs">
            <div class="flex justify-between text-gray-600">
                <span>Subtotal Produk</span>
                <span class="font-mono font-bold">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>
            
            <div class="flex justify-between text-gray-600">
                <span>Biaya Pengiriman ({{ strtoupper($order->fulfillment_type) }})</span>
                <span class="font-mono font-bold">Rp {{ number_format($order->delivery_fee, 0, ',', '.') }}</span>
            </div>

            <div class="flex justify-between text-gray-600">
                <span>Biaya Layanan & Operasional Hyperlocal</span>
                <span class="font-mono font-bold">Rp {{ number_format($order->service_fee, 0, ',', '.') }}</span>
            </div>

            @if($order->discount_amount > 0)
                <div class="flex justify-between text-emerald-600 font-semibold">
                    <span>Diskon & Voucher Loyalty</span>
                    <span class="font-mono">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                </div>
            @endif

            <div class="border-t border-gray-200 pt-3 flex justify-between items-baseline">
                <div>
                    <span class="font-black text-sm text-[#1E2723] block">TOTAL PEMBAYARAN</span>
                    <span class="text-[10px] text-gray-400">Termasuk PPN & Biaya Penanganan</span>
                </div>
                <span class="font-mono font-black text-lg text-[#0B5A45]">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- QR Verification / Footer Struk -->
        <div class="border-t border-dashed border-gray-200 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
            <div class="text-[11px] text-gray-400 space-y-0.5">
                <p class="font-bold text-gray-600">Struk digital ini sah dan diterbitkan secara elektronik.</p>
                <p>Simpan dokumen ini sebagai bukti pembayaran dan jaminan perlindungan konsumen TokoKita.</p>
            </div>
            
            <div class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-center">
                <span class="text-[9px] font-mono font-bold text-gray-400 block uppercase">Kode Validasi Transaksi</span>
                <span class="text-xs font-mono font-black text-[#0B5A45] tracking-wider">{{ substr(md5($order->order_number . $order->created_at), 0, 10) }}</span>
            </div>
        </div>

    </div>

</div>
@endsection
