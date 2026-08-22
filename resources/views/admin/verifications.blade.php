@extends('layouts.admin')

@section('title', 'Verifikasi Pendaftaran Mitra — Admin Toko Kita')

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="font-display font-black text-2xl text-[#0B5A45]">Antrean & Daftar Mitra UMKM</h1>
        <p class="text-xs text-gray-500">Tinjau kelayakan berkas pendaftaran mitra warung/toko sebelum diizinkan berjualan.</p>
    </div>

    <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-gray-400 border-b border-gray-100">
                        <th class="pb-3 font-semibold">NAMA TOKO & PEMILIK</th>
                        <th class="pb-3 font-semibold">KATEGORI</th>
                        <th class="pb-3 font-semibold">ALAMAT & KOTA</th>
                        <th class="pb-3 font-semibold">LEGALITAS (NIB)</th>
                        <th class="pb-3 font-semibold">STATUS</th>
                        <th class="pb-3 font-semibold text-right">AKSI VERIFIKASI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($stores as $st)
                        <tr class="hover:bg-[#FAF8F2]/60 transition">
                            <td class="py-3.5">
                                <span class="font-bold text-gray-800 text-sm block">{{ $st->name }}</span>
                                <span class="text-gray-400 text-[11px]">{{ $st->user->name }} ({{ $st->phone }})</span>
                            </td>
                            <td class="py-3.5">
                                <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-semibold text-[10px]">{{ $st->category->name ?? 'UMKM' }}</span>
                            </td>
                            <td class="py-3.5 max-w-[200px] truncate text-gray-600">
                                {{ $st->address }}, {{ $st->city }}
                            </td>
                            <td class="py-3.5 font-mono text-gray-600">
                                {{ $st->nib_number ?: 'Tanpa NIB (Mikro)' }}
                            </td>
                            <td class="py-3.5">
                                @if($st->status === 'approved')
                                    <span class="bg-emerald-100 text-[#0E9F6E] px-2 py-0.5 rounded-full font-bold uppercase text-[10px]">APPROVED</span>
                                @elseif($st->status === 'pending')
                                    <span class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full font-bold uppercase text-[10px]">PENDING</span>
                                @else
                                    <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-bold uppercase text-[10px]">REJECTED</span>
                                @endif
                            </td>
                            <td class="py-3.5 text-right space-x-1">
                                @if($st->status === 'pending' || $st->status === 'rejected')
                                    <form action="{{ route('admin.verifications.process', $st->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="bg-[#0E9F6E] hover:bg-[#086644] text-white px-3 py-1.5 rounded-xl font-bold transition">Setujui</button>
                                    </form>
                                @endif

                                @if($st->status === 'pending' || $st->status === 'approved')
                                    <form action="{{ route('admin.verifications.process', $st->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="rejection_reason" value="Data belum lengkap / Tidak memenuhi kriteria.">
                                        <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 px-3 py-1.5 rounded-xl font-bold transition">Tolak</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $stores->links() }}
        </div>
    </div>

</div>
@endsection
