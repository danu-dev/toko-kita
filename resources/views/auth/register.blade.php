@extends('layouts.app')

@section('title', 'Daftar Akun Pembeli — Toko Kita')

@section('content')
<div class="max-w-md mx-auto my-8 bg-white p-6 sm:p-8 rounded-3xl border border-gray-100 shadow-xl">
    <div class="text-center mb-6">
        <div class="w-12 h-12 rounded-2xl bg-[#0E9F6E] flex items-center justify-center text-white mx-auto mb-3 shadow-md shadow-[#0E9F6E]/30">
            <i data-lucide="user-plus" class="w-6 h-6"></i>
        </div>
        <h1 class="font-display font-black text-2xl text-[#0B5A45]">Buat Akun Pembeli</h1>
        <p class="text-xs text-gray-500 mt-1">Daftar untuk menikmati jajanan dan produk UMKM terdekat</p>
    </div>

    <form action="{{ route('register') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#1E2723] mb-1.5">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#0E9F6E]" placeholder="Budi Santoso">
            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#1E2723] mb-1.5">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#0E9F6E]" placeholder="budi@example.com">
            @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#1E2723] mb-1.5">Nomor WhatsApp / HP</label>
            <input type="tel" name="phone" value="{{ old('phone') }}" required class="w-full px-4 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#0E9F6E]" placeholder="081234567890">
            @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#1E2723] mb-1.5">Password</label>
            <input type="password" name="password" required class="w-full px-4 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#0E9F6E]" placeholder="Minimal 6 karakter">
            @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#1E2723] mb-1.5">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" required class="w-full px-4 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#0E9F6E]" placeholder="Ulangi password">
        </div>

        <button type="submit" class="w-full bg-[#0E9F6E] hover:bg-[#086644] text-white font-bold py-3 rounded-2xl shadow-md shadow-[#0E9F6E]/20 transition flex items-center justify-center gap-2">
            <span>Daftar Sekarang</span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </button>
    </form>

    <div class="mt-6 text-center text-xs text-gray-500">
        Sudah punya akun? <a href="{{ route('login') }}" class="text-[#0E9F6E] font-bold hover:underline">Masuk di sini</a>
    </div>
</div>
@endsection
