@extends('layouts.admin')

@section('title', 'Penanganan Dispute & Komplain — Admin Toko Kita')

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="font-display font-black text-2xl text-[#0B5A45]">Pusat Resolusi Dispute & Komplain</h1>
        <p class="text-xs text-gray-500">Tinjau keluhan pembeli terhadap pesanan dan tentukan keputusan pengembalian dana (refund) atau penolakan.</p>
    </div>

    <div class="space-y-4">
        @forelse($disputes as $disp)
            <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm space-y-4">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 text-xs">
                    <div class="flex items-center gap-3">
                        <span class="font-mono font-bold text-gray-900 bg-red-50 text-red-700 px-2.5 py-1 rounded-lg">
                            DISPUTE: {{ $disp->order->order_number }}
                        </span>
                        <span class="text-gray-400">• {{ $disp->created_at->format('d M Y, H:i') }}</span>
                    </div>

                    <span class="font-bold text-xs uppercase px-2.5 py-1 rounded-full {{ str_starts_with($disp->status, 'resolved') ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                        {{ $disp->status }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="space-y-1.5 bg-[#FAF8F2] p-4 rounded-2xl">
                        <span class="font-bold text-[#0B5A45] uppercase text-[11px] block">Keluhan Pembeli ({{ $disp->buyer->name }}):</span>
                        <p class="font-semibold text-gray-900">"{{ $disp->reason }}"</p>
                        <p class="text-gray-600">{{ $disp->description }}</p>
                        <span class="font-mono font-bold text-[#0B5A45] block mt-2">Nilai Order: Rp {{ number_format($disp->order->total, 0, ',', '.') }}</span>
                    </div>

                    <div class="space-y-2 flex flex-col justify-between">
                        <div>
                            <span class="font-bold text-gray-700 uppercase text-[11px] block">Toko Terkait:</span>
                            <p class="font-bold text-[#1E2723]">{{ $disp->store->name }} ({{ $disp->store->phone }})</p>
                            @if($disp->admin_decision)
                                <div class="mt-2 p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                                    <span class="font-bold text-[#0B5A45] block">Keputusan Admin:</span>
                                    <p class="text-gray-700">{{ $disp->admin_decision }}</p>
                                </div>
                            @endif
                        </div>

                        @if(!str_starts_with($disp->status, 'resolved'))
                            <div class="flex items-center gap-2 pt-2">
                                <form action="{{ route('admin.disputes.resolve', $disp->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="decision" value="refund">
                                    <input type="hidden" name="admin_decision" value="Komplain disetujui: Dana dikembalikan ke pembeli.">
                                    <button type="submit" class="w-full bg-[#0E9F6E] hover:bg-[#086644] text-white font-bold py-2 rounded-xl text-xs shadow-xs">
                                        Setujui Refund
                                    </button>
                                </form>
                                <form action="{{ route('admin.disputes.resolve', $disp->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="decision" value="reject_dispute">
                                    <input type="hidden" name="admin_decision" value="Komplain ditolak: Tidak ditemukan bukti kesalahan dari toko.">
                                    <button type="submit" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 rounded-xl text-xs">
                                        Tolak Komplain
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        @empty
            <div class="bg-white p-12 text-center rounded-3xl border border-gray-100 text-gray-400">
                Tidak ada sengketa / komplain aktif.
            </div>
        @endforelse

        <div class="mt-4">
            {{ $disputes->links() }}
        </div>
    </div>

</div>
@endsection
