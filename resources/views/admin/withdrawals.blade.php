@extends('layouts.admin')

@section('title', 'Persetujuan Pencairan Dana — Admin Toko Kita')

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="font-display font-black text-2xl text-[#0B5A45]">Persetujuan Penarikan Dana Mitra</h1>
        <p class="text-xs text-gray-500">Otorisasi transfer pencairan saldo dompet toko ke nomor rekening bank mitra UMKM.</p>
    </div>

    <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-gray-400 border-b border-gray-100">
                        <th class="pb-3">TANGGAL</th>
                        <th class="pb-3">TOKO & PEMILIK</th>
                        <th class="pb-3">REKENING TUJUAN</th>
                        <th class="pb-3">NOMINAL CAIR</th>
                        <th class="pb-3">STATUS</th>
                        <th class="pb-3 text-right">AKSI APPROVAL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($withdrawals as $w)
                        <tr class="hover:bg-[#FAF8F2]/60 transition">
                            <td class="py-3.5 font-mono text-gray-700">{{ $w->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-3.5">
                                <span class="font-bold text-gray-800">{{ $w->store->name }}</span>
                                <span class="block text-[11px] text-gray-400">{{ $w->store->user->name }}</span>
                            </td>
                            <td class="py-3.5">
                                <b>{{ $w->bank_name }}</b> — {{ $w->account_number }}
                                <span class="block text-[10px] text-gray-400">a.n {{ $w->account_holder }}</span>
                            </td>
                            <td class="py-3.5 font-mono font-black text-sm text-[#0B5A45]">
                                Rp {{ number_format($w->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5">
                                @if($w->status === 'approved')
                                    <span class="bg-emerald-100 text-[#0E9F6E] px-2.5 py-0.5 rounded-full font-bold uppercase text-[10px]">DISETUJUI</span>
                                @elseif($w->status === 'pending')
                                    <span class="bg-amber-100 text-amber-800 px-2.5 py-0.5 rounded-full font-bold uppercase text-[10px]">PENDING</span>
                                @else
                                    <span class="bg-red-100 text-red-700 px-2.5 py-0.5 rounded-full font-bold uppercase text-[10px]">DITOLAK</span>
                                @endif
                            </td>
                            <td class="py-3.5 text-right space-x-1">
                                @if($w->status === 'pending')
                                    <form action="{{ route('admin.withdrawals.process', $w->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="bg-[#0E9F6E] hover:bg-[#086644] text-white px-3 py-1.5 rounded-xl font-bold transition">Setujui & Transfer</button>
                                    </form>
                                    <form action="{{ route('admin.withdrawals.process', $w->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-1.5 rounded-xl font-bold transition">Tolak</button>
                                    </form>
                                @else
                                    <span class="text-gray-400 font-mono text-[11px]">{{ $w->processed_at?->format('d/m H:i') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-400">Belum ada data pengajuan pencairan dana.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $withdrawals->links() }}
        </div>
    </div>

</div>
@endsection
