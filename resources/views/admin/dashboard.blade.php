@extends('layouts.admin')

@section('title', 'Dashboard Platform Analytics — Toko Kita')

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="font-display font-black text-2xl text-[#0B5A45]">Platform Executive Dashboard</h1>
        <p class="text-xs text-gray-500">Ringkasan transaksi GMV, pendapatan komisi, dan antrean operasional seluruh mitra.</p>
    </div>

    <!-- Platform Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm space-y-2">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Gross Merchandise Value (GMV)</span>
            <div class="font-mono font-black text-2xl text-[#0B5A45]">
                Rp {{ number_format($gmv, 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-emerald-600 font-semibold">Total omset perputaran UMKM</span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm space-y-2">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Pendapatan Platform (5%)</span>
            <div class="font-mono font-black text-2xl text-[#F2A93B]">
                Rp {{ number_format($platformRevenue, 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-gray-400">Komisi bersih platform</span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm space-y-2">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Mitra & Transaksi</span>
            <div class="font-mono font-black text-2xl text-gray-800">
                {{ $totalStores }} Toko / {{ $totalOrders }} Order
            </div>
            <span class="text-[11px] text-gray-400">Mitra aktif terdaftar</span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm space-y-2">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Antrean Perlu Tindakan</span>
            <div class="flex items-center gap-2 font-mono font-black text-xl text-red-600">
                <span>{{ $pendingStoresCount + $pendingWithdrawalsCount + $openDisputesCount }} Tugas</span>
            </div>
            <div class="flex items-center gap-2 text-[10px] text-gray-500 font-bold">
                <span>{{ $pendingStoresCount }} Toko</span> • <span>{{ $pendingWithdrawalsCount }} Tarik</span> • <span>{{ $openDisputesCount }} Dispute</span>
            </div>
        </div>

    </div>

    <!-- Operational Queues Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left: Pending Mitra Verifications Queue (PRD Section 3.3) -->
        <div class="lg:col-span-6 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="user-check" class="w-5 h-5 text-[#F2A93B]"></i>
                    <h3 class="font-display font-bold text-base text-[#0B5A45]">Antrean Verifikasi Mitra Baru</h3>
                </div>
                <a href="{{ route('admin.verifications') }}" class="text-xs text-[#0E9F6E] font-bold hover:underline">Semua &rarr;</a>
            </div>

            <div class="divide-y divide-gray-100 space-y-3">
                @forelse($pendingStores as $pStore)
                    <div class="pt-3 first:pt-0 flex items-center justify-between gap-3 text-xs">
                        <div>
                            <h4 class="font-bold text-gray-800">{{ $pStore->name }}</h4>
                            <p class="text-[11px] text-gray-400">{{ $pStore->user->name }} • {{ $pStore->address }}</p>
                            @if($pStore->nib_number)
                                <span class="inline-block bg-blue-50 text-blue-700 text-[10px] font-mono px-2 py-0.5 rounded mt-0.5">NIB: {{ $pStore->nib_number }}</span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            <form action="{{ route('admin.verifications.process', $pStore->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="bg-[#0E9F6E] hover:bg-[#086644] text-white px-3 py-1.5 rounded-xl font-bold text-xs shadow-xs">
                                    Setujui
                                </button>
                            </form>
                            <form action="{{ route('admin.verifications.process', $pStore->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="bg-red-50 text-red-600 px-3 py-1.5 rounded-xl font-bold text-xs">
                                    Tolak
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 text-center py-6">Tidak ada antrean verifikasi tertunda. Semua mitra telah ditinjau!</p>
                @endforelse
            </div>
        </div>

        <!-- Right: Recent Platform Transactions Stream -->
        <div class="lg:col-span-6 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="activity" class="w-5 h-5 text-[#0E9F6E]"></i>
                    <h3 class="font-display font-bold text-base text-[#0B5A45]">Aktivitas Transaksi Real-time</h3>
                </div>
                <a href="{{ route('admin.transactions') }}" class="text-xs text-[#0E9F6E] font-bold hover:underline">Monitoring &rarr;</a>
            </div>

            <div class="divide-y divide-gray-100 space-y-3">
                @foreach($recentOrders as $ro)
                    <div class="pt-3 first:pt-0 flex items-center justify-between text-xs">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-gray-800">{{ $ro->order_number }}</span>
                                <span class="text-gray-400">• {{ $ro->created_at->format('H:i') }}</span>
                            </div>
                            <p class="text-gray-500 mt-0.5"><b>{{ $ro->buyer->name }}</b> belanja di <b>{{ $ro->store->name }}</b></p>
                        </div>

                        <div class="text-right">
                            <span class="font-mono font-bold text-[#0B5A45] block">Rp {{ number_format($ro->total, 0, ',', '.') }}</span>
                            <x-status-pulse :status="$ro->status" />
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

</div>
@endsection
