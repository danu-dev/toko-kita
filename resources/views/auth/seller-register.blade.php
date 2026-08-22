@extends('layouts.app')

@section('title', 'Pendaftaran Mitra UMKM — Toko Kita')

@section('content')
<div class="max-w-2xl mx-auto my-8 bg-white p-6 sm:p-8 rounded-3xl border border-gray-100 shadow-xl">
    <div class="text-center mb-6">
        <div class="w-12 h-12 rounded-2xl bg-[#F2A93B] flex items-center justify-center text-[#1E2723] mx-auto mb-3 shadow-md shadow-[#F2A93B]/30">
            <i data-lucide="store" class="w-6 h-6"></i>
        </div>
        <h1 class="font-display font-black text-2xl text-[#0B5A45]">Daftarkan Toko / Warung UMKM</h1>
        <p class="text-xs text-gray-500 mt-1">Jangkau ribuan pelanggan di sekitarmu dengan komisi terjangkau (5%)</p>
    </div>

    <form action="{{ route('seller.register.submit') }}" method="POST" class="space-y-4">
        @csrf
        <div class="bg-[#FAF8F2] p-4 rounded-2xl border border-gray-200/80 mb-4">
            <h2 class="text-xs font-bold text-[#0B5A45] uppercase tracking-wider mb-3">1. Data Pemilik Toko</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap Pemilik</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]" placeholder="Nama Anda">
                    @error('name') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]" placeholder="email@warung.com">
                    @error('email') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor WhatsApp Toko</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]" placeholder="08123456789">
                    @error('phone') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Password Akun</label>
                    <input type="password" name="password" required class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]" placeholder="••••••••">
                    @error('password') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]" placeholder="Ulangi password">
                </div>
            </div>
        </div>

        <div class="bg-[#FAF8F2] p-4 rounded-2xl border border-gray-200/80">
            <h2 class="text-xs font-bold text-[#0B5A45] uppercase tracking-wider mb-3">2. Profil Usaha / Toko</h2>
            <div class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Usaha / Warung</label>
                        <input type="text" name="store_name" value="{{ old('store_name') }}" required class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]" placeholder="Contoh: Warung Nasi Bu Siti">
                        @error('store_name') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Kategori Produk Utama</label>
                        <select name="category_id" required class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat Lengkap Toko</label>
                    <textarea name="address" rows="2" required class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]" placeholder="Jl. Soekarno Hatta No. 12, Lowokwaru, Malang">{{ old('address') }}</textarea>
                    @error('address') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Operasional</label>
                        <input type="text" name="operational_hours" value="{{ old('operational_hours', '08:00 - 21:00') }}" required class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">NIB / No. Izin Usaha (Opsional)</label>
                        <input type="text" name="nib_number" value="{{ old('nib_number') }}" class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]" placeholder="912000xxxxxxx">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Deskripsi Singkat Toko</label>
                    <textarea name="description" rows="2" class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]" placeholder="Ceritakan keistimewaan jualan Anda...">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="w-full bg-[#F2A93B] hover:bg-[#D88E23] text-[#1E2723] font-bold py-3.5 rounded-2xl shadow-md shadow-[#F2A93B]/20 transition flex items-center justify-center gap-2 text-sm">
            <span>Kirim Pendaftaran Mitra</span>
            <i data-lucide="send" class="w-4 h-4"></i>
        </button>
    </form>
</div>
@endsection
