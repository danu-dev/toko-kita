@extends('layouts.app')

@section('title', 'Masuk Akun — Toko Kita')

@section('content')
<div class="max-w-md mx-auto my-8 bg-white p-6 sm:p-8 rounded-3xl border border-gray-100 shadow-xl">
    <div class="text-center mb-6">
        <div class="w-12 h-12 rounded-2xl bg-[#0E9F6E] flex items-center justify-center text-white mx-auto mb-3 shadow-md shadow-[#0E9F6E]/30">
            <i data-lucide="log-in" class="w-6 h-6"></i>
        </div>
        <h1 class="font-display font-black text-2xl text-[#0B5A45]">Selamat Datang</h1>
        <p class="text-xs text-gray-500 mt-1">Masuk untuk mulai belanja atau kelola tokomu</p>
    </div>

    <form action="{{ route('login') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#1E2723] mb-1.5">Email / No. WhatsApp</label>
            <input type="text" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#0E9F6E] focus:ring-2 focus:ring-[#0E9F6E]/20" placeholder="nama@email.com">
            @error('email')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-[#1E2723]">Password</label>
            </div>
            <input type="password" name="password" required class="w-full px-4 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#0E9F6E] focus:ring-2 focus:ring-[#0E9F6E]/20" placeholder="••••••••">
        </div>

        <div class="flex items-center justify-between text-xs">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="remember" class="rounded text-[#0E9F6E] focus:ring-[#0E9F6E]">
                <span>Ingat saya</span>
            </label>
        </div>

        <button type="submit" class="w-full bg-[#0E9F6E] hover:bg-[#086644] text-white font-bold py-3 rounded-2xl shadow-md shadow-[#0E9F6E]/20 transition flex items-center justify-center gap-2">
            <span>Masuk Sekarang</span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </button>
    </form>

    <div class="mt-6 text-center text-xs text-gray-500">
        Belum punya akun? <a href="{{ route('register') }}" class="text-[#0E9F6E] font-bold hover:underline">Daftar Pembeli</a> atau <a href="{{ route('seller.register') }}" class="text-[#F2A93B] font-bold hover:underline">Daftar Jadi Mitra Toko</a>
    </div>
</div>
@endsection
