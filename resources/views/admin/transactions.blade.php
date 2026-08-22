@extends('layouts.admin')

@section('title', 'Monitoring Transaksi — Admin Toko Kita')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-black text-2xl text-[#0B5A45]">Monitoring Transaksi Real-time</h1>
            <p class="text-xs text-gray-500">Pantau seluruh order lintas toko, cegah dispute dan keterlambatan pengiriman.</p>
        </div>

        <div class="flex items-center gap-1.5 overflow-x-auto text-xs bg-white p-1.5 rounded-2xl border border-gray-100 shadow-xs">
            <a href="{{ route('admin.transactions') }}" class="px-3 py-1.5 rounded-xl font-bold {{ empty($status) ? 'bg-[#0E9F6E] text-white' : 'text-gray-600 hover:bg-gray-100' }}">Semua</a>
            <a href="{{ route('admin.transactions', ['status' => 'diproses']) }}" class="px-3 py-1.5 rounded-xl font-bold {{ $status === 'diproses' ? 'bg-[#0E9F6E] text-white' : 'text-gray-600 hover:bg-gray-100' }}">Diproses</a>
            <a href="{{ route('admin.transactions', ['status' => 'selesai']) }}" class="px-3 py-1.5 rounded-xl font-bold {{ $status === 'selesai' ? 'bg-[#0E9F6E] text-white' : 'text-gray-600 hover:bg-gray-100' }}">Selesai</a>
            <a href="{{ route('admin.transactions', ['status' => 'dibatalkan']) }}" class="px-3 py-1.5 rounded-xl font-bold {{ $status === 'dibatalkan' ? 'bg-red-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Dibatalkan</a>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-gray-400 border-b border-gray-100">
                        <th class="pb-3">NO. ORDER</th>
                        <th class="pb-3">PEMBELI & TOKO</th>
                        <th class="pb-3">TOTAL TRANSAKSI</th>
                        <th class="pb-3">KOMISI PLATFORM</th>
                        <th class="pb-3">METODE BAYAR</th>
                        <th class="pb-3">STATUS PULSE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($orders as $o)
                        <tr class="hover:bg-[#FAF8F2]/60 transition">
                            <td class="py-3.5 font-mono font-bold text-gray-800">
                                {{ $o->order_number }}
                                <span class="block text-[10px] text-gray-400 font-sans font-normal">{{ $o->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="py-3.5">
                                <span class="font-bold text-gray-800">{{ $o->buyer->name }}</span>
                                <span class="block text-[11px] text-gray-400">&rarr; Toko: {{ $o->store->name }}</span>
                            </td>
                            <td class="py-3.5 font-mono font-bold text-[#0B5A45]">
                                Rp {{ number_format($o->total, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 font-mono font-bold text-[#F2A93B]">
                                Rp {{ number_format($o->commission_fee, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5">
                                <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-bold uppercase text-[10px]">{{ $o->payment?->method ?? 'QRIS' }}</span>
                            </td>
                            <td class="py-3.5">
                                <x-status-pulse :status="$o->status" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>

</div>
@endsection
