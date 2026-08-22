@extends('layouts.app')

@section('title', 'Pusat Bantuan & FAQ — TokoKita.')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    <!-- Header Section -->
    <div class="text-center space-y-3 bg-gradient-to-b from-[#EDFDF5] to-transparent p-8 rounded-3xl border border-emerald-100">
        <span class="bg-[#0E9F6E] text-white text-xs font-bold px-3.5 py-1 rounded-full uppercase tracking-wider">Pusat Bantuan & Panduan</span>
        <h1 class="font-display font-black text-3xl sm:text-4xl text-[#0B5A45]">Ada yang bisa kami bantu?</h1>
        <p class="text-xs sm:text-sm text-gray-600 max-w-lg mx-auto leading-relaxed">
            Temukan jawaban atas pertanyaan umum seputar pemesanan hyperlocal, pembayaran escrow, pengiriman kurir lokal, dan kemitraan UMKM.
        </p>
    </div>

    <!-- Quick Help Categories Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm space-y-2">
            <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-[#0E9F6E] flex items-center justify-center">
                <i data-lucide="shopping-cart" class="w-5 h-5"></i>
            </div>
            <h3 class="font-bold text-sm text-gray-900">Pemesanan & Antar</h3>
            <p class="text-xs text-gray-500">Panduan order, estimasi SLA waktu masak, dan pilihan pickup/delivery.</p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm space-y-2">
            <div class="w-10 h-10 rounded-2xl bg-amber-50 text-[#F2A93B] flex items-center justify-center">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
            <h3 class="font-bold text-sm text-gray-900">Escrow & Garansi</h3>
            <p class="text-xs text-gray-500">Keamanan pembayaran 100% dan garansi uang kembali bila pesanan bermasalah.</p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm space-y-2">
            <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <i data-lucide="store" class="w-5 h-5"></i>
            </div>
            <h3 class="font-bold text-sm text-gray-900">Mitra UMKM</h3>
            <p class="text-xs text-gray-500">Cara buka toko online gratis, penarikan saldo dompet cepat, dan promosi.</p>
        </div>
    </div>

    <!-- Accordion FAQ Section -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
            <h2 class="font-display font-bold text-lg text-[#0B5A45] flex items-center gap-2">
                <i data-lucide="help-circle" class="w-5 h-5 text-[#0E9F6E]"></i>
                <span>Pertanyaan Sering Ditanyakan (FAQ)</span>
            </h2>
        </div>

        <div class="space-y-3" x-data="{ active: null }">
            @foreach($faqs as $index => $faq)
                <div class="border border-gray-100 rounded-2xl overflow-hidden transition bg-[#FAF8F2]/50">
                    <button type="button" @click="active = (active === {{ $index }} ? null : {{ $index }})" class="w-full p-4 text-left flex items-center justify-between gap-4 font-bold text-xs sm:text-sm text-gray-800 hover:text-[#0E9F6E] transition">
                        <span>{{ $faq['q'] }}</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="active === {{ $index }} ? 'rotate-180 text-[#0E9F6E]' : ''"></i>
                    </button>
                    <div x-show="active === {{ $index }}" x-cloak class="px-4 pb-4 pt-1 text-xs text-gray-600 leading-relaxed border-t border-gray-100/60 bg-white">
                        {{ $faq['a'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Direct Contact Support Card -->
    <div class="bg-[#0B5A45] text-white p-6 sm:p-8 rounded-3xl shadow-lg flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="space-y-1 text-center sm:text-left">
            <h3 class="font-display font-black text-xl text-[#F2A93B]">Butuh Bantuan Lebih Lanjut?</h3>
            <p class="text-xs text-emerald-100/80">Tim TokoKita Support siap membantu kendala operasional dan transaksi Anda.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('chats.index') }}" class="bg-[#0E9F6E] hover:bg-[#086644] text-white px-5 py-2.5 rounded-2xl text-xs font-bold shadow-md transition flex items-center gap-2">
                <i data-lucide="message-square" class="w-4 h-4"></i>
                <span>Chat Dukungan Live</span>
            </a>
        </div>
    </div>

</div>
@endsection
