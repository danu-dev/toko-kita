@extends('layouts.seller')

@section('title', 'Saldo & Penarikan Dana — ' . $store->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <h1 class="font-display font-black text-2xl text-[#0B5A45]">Dompet & Pencairan Dana Mitra</h1>
        <p class="text-xs text-gray-500">Kelola penghasilan bersih penjualan toko dan ajukan transfer ke rekening bank Anda.</p>
    </div>

    <!-- Balance Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-2 relative overflow-hidden">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Saldo Tersedia (Siap Dicairkan)</span>
            <div class="font-mono font-black text-3xl text-[#0B5A45]">
                Rp {{ number_format($wallet->balance, 0, ',', '.') }}
            </div>
            <p class="text-xs text-gray-400">Minimal penarikan Rp 20.000 (Bebas biaya admin transfer)</p>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-2">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Saldo Tertahan (Anti-Fraud Hold)</span>
            <div class="font-mono font-black text-3xl text-[#F2A93B]">
                Rp {{ number_format($wallet->held_balance, 0, ',', '.') }}
            </div>
            <p class="text-xs text-gray-400">Otomatis masuk ke saldo aktif 1x24 jam setelah pesanan selesai.</p>
        </div>

    </div>

    <!-- Withdrawal Request Form -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-100 shadow-sm space-y-4">
        <h3 class="font-display font-bold text-base text-[#0B5A45]">Ajukan Pencairan Dana ke Rekening Bank</h3>

        @if($wallet->balance < 20000)
            <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 text-xs text-amber-900">
                Saldo Anda belum mencapai batas minimal penarikan (Rp 20.000).
            </div>
        @else
            <form action="{{ route('seller.wallet.withdraw') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Nominal Penarikan (Rp)</label>
                        <input type="number" name="amount" min="20000" max="{{ $wallet->balance }}" value="{{ old('amount', (int)$wallet->balance) }}" required class="w-full px-3.5 py-2 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm font-mono font-bold focus:border-[#0E9F6E]">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Nama Bank / E-Wallet</label>
                        <select name="bank_name" required class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
                            <option value="BCA">Bank BCA</option>
                            <option value="BRI">Bank BRI</option>
                            <option value="Mandiri">Bank Mandiri</option>
                            <option value="BNI">Bank BNI</option>
                            <option value="Bank Jago">Bank Jago</option>
                            <option value="GoPay">GoPay (E-Wallet)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Nomor Rekening / No. HP E-Wallet</label>
                        <input type="text" name="account_number" required placeholder="Contoh: 1234567890" class="w-full px-3.5 py-2 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Nama Pemilik Rekening</label>
                        <input type="text" name="account_holder" required placeholder="Sesuai buku tabungan" class="w-full px-3.5 py-2 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
                    </div>
                </div>

                <button type="submit" class="bg-[#0E9F6E] hover:bg-[#086644] text-white font-bold px-6 py-3 rounded-2xl text-xs shadow-md shadow-[#0E9F6E]/20 transition">
                    Kirim Pengajuan Pencairan
                </button>
            </form>
        @endif
    </div>

    <!-- Withdrawal Histories -->
    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
        <h3 class="font-display font-bold text-base text-[#0B5A45]">Riwayat Pencairan Dana</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-gray-400 border-b border-gray-100">
                        <th class="pb-3">TANGGAL</th>
                        <th class="pb-3">BANK & REKENING</th>
                        <th class="pb-3">NOMINAL</th>
                        <th class="pb-3">STATUS</th>
                        <th class="pb-3">CATATAN ADMIN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($withdrawals as $w)
                        <tr>
                            <td class="py-3 font-mono text-gray-700">{{ $w->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-3">
                                <b>{{ $w->bank_name }}</b> — {{ $w->account_number }}
                                <span class="block text-[10px] text-gray-400">a.n {{ $w->account_holder }}</span>
                            </td>
                            <td class="py-3 font-mono font-bold text-[#0B5A45]">Rp {{ number_format($w->amount, 0, ',', '.') }}</td>
                            <td class="py-3">
                                @if($w->status === 'approved')
                                    <span class="bg-emerald-100 text-[#0E9F6E] px-2 py-0.5 rounded font-bold uppercase text-[10px]">DISETUJUI</span>
                                @elseif($w->status === 'pending')
                                    <span class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded font-bold uppercase text-[10px]">MENUNGGU</span>
                                @else
                                    <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded font-bold uppercase text-[10px]">DITOLAK</span>
                                @endif
                            </td>
                            <td class="py-3 text-gray-500">{{ $w->admin_notes ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-400">Belum ada riwayat penarikan dana.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
