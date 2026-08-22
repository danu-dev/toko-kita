@extends('layouts.seller')

@section('title', 'Laporan & Ulasan — ' . $store->name)

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="font-display font-black text-2xl text-[#0B5A45]">Laporan Penjualan & Ulasan Toko</h1>
            <p class="text-xs text-gray-500">Pantau performa harian omset toko dan respon ulasan dari pelanggan.</p>
        </div>
        <div>
            <a href="{{ route('seller.reports.export-csv') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0B5A45] hover:bg-[#084233] text-white text-xs font-bold rounded-2xl shadow-sm hover:shadow transition-all">
                <i data-lucide="download" class="w-4 h-4 text-[#F2A93B]"></i>
                <span>Export Laporan Transaksi (.CSV)</span>
            </a>
        </div>
    </div>

    <!-- Sales Trend Table -->
    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
        <h3 class="font-display font-bold text-base text-[#0B5A45]">Grafik / Ringkasan Penjualan 14 Hari Terakhir</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-gray-400 border-b border-gray-100">
                        <th class="pb-3">TANGGAL</th>
                        <th class="pb-3">PESANAN SELESAI</th>
                        <th class="pb-3">TOTAL PENDAPATAN BERSIH</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($dailySales as $sale)
                        <tr>
                            <td class="py-3 font-mono font-bold text-gray-800">{{ date('d M Y', strtotime($sale->date)) }}</td>
                            <td class="py-3 font-mono text-gray-700">{{ $sale->total_orders }} Transaksi</td>
                            <td class="py-3 font-mono font-bold text-[#0B5A45]">Rp {{ number_format($sale->total_earnings, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-6 text-center text-gray-400">Belum ada data penjualan tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Customer Reviews & Reply Section -->
    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
        <h3 class="font-display font-bold text-base text-[#0B5A45]">Ulasan Masuk & Balasan Toko</h3>

        <div class="space-y-4 divide-y divide-gray-100">
            @forelse($reviews as $rev)
                <div class="pt-4 first:pt-0 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-gray-800">{{ $rev->buyer->name }}</span>
                            <span class="text-gray-400">• {{ $rev->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="flex items-center gap-1 text-[#F2A93B]">
                            @for($i=1; $i<=5; $i++)
                                <i data-lucide="star" class="w-3.5 h-3.5 {{ $i <= $rev->rating ? 'fill-current' : 'text-gray-200' }}"></i>
                            @endfor
                        </div>
                    </div>

                    <p class="text-gray-700">{{ $rev->comment }}</p>

                    @if($rev->seller_reply)
                        <div class="bg-[#FAF8F2] p-3 rounded-2xl border-l-2 border-[#0E9F6E] space-y-0.5">
                            <span class="font-bold text-[#0E9F6E] block text-[11px]">Balasan Anda:</span>
                            <p class="text-gray-600">{{ $rev->seller_reply }}</p>
                        </div>
                    @else
                        <form action="{{ route('seller.reviews.reply', $rev->id) }}" method="POST" class="flex gap-2 pt-1">
                            @csrf
                            <input type="text" name="seller_reply" required placeholder="Balas ulasan pembeli..." class="flex-1 px-3 py-1.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-xs">
                            <button type="submit" class="bg-[#0E9F6E] hover:bg-[#086644] text-white px-3.5 py-1.5 rounded-xl font-bold text-xs">Kirim Balasan</button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="text-center py-6 text-gray-400">Belum ada ulasan dari pembeli.</div>
            @endforelse
        </div>
    </div>

</div>
@endsection
