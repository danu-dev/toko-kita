@extends('layouts.seller')

@section('title', 'Pengaturan Toko — ' . $store->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div>
        <h1 class="font-display font-black text-2xl text-[#0B5A45]">Pengaturan Profil Toko</h1>
        <p class="text-xs text-gray-500">Perbarui jam buka, alamat, status buka/tutup, logo, dan banner tokomu (upload file atau URL).</p>
    </div>

    <form action="{{ route('seller.settings.update') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-100 shadow-sm space-y-4 text-xs" x-data="{ logoMode: 'upload', bannerMode: 'upload' }">
        @csrf
        @method('PUT')

        <div>
            <label class="block font-bold text-gray-700 uppercase mb-1">Nama Toko</label>
            <input type="text" name="name" value="{{ old('name', $store->name) }}" required class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
        </div>

        <div>
            <label class="block font-bold text-gray-700 uppercase mb-1">Jam Operasional</label>
            <input type="text" name="operational_hours" value="{{ old('operational_hours', $store->operational_hours) }}" required class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
        </div>

        <div>
            <label class="block font-bold text-gray-700 uppercase mb-1">No. WhatsApp Toko</label>
            <input type="text" name="phone" value="{{ old('phone', $store->phone) }}" required class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
        </div>

        <div>
            <label class="block font-bold text-gray-700 uppercase mb-1">Alamat Lengkap</label>
            <textarea name="address" rows="2" required class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">{{ old('address', $store->address) }}</textarea>
        </div>

        <!-- Logo Image Picker -->
        <div class="space-y-2 bg-[#FAF8F2] p-4 rounded-2xl border border-gray-200">
            <div class="flex items-center justify-between">
                <label class="block font-bold text-gray-700 uppercase">Logo Toko</label>
                <div class="flex items-center gap-1 bg-white p-0.5 rounded-lg border border-gray-200 text-xs">
                    <button type="button" @click="logoMode = 'upload'" class="px-2.5 py-0.5 rounded-md font-semibold" :class="logoMode === 'upload' ? 'bg-[#0E9F6E] text-white' : 'text-gray-600'">Upload File</button>
                    <button type="button" @click="logoMode = 'url'" class="px-2.5 py-0.5 rounded-md font-semibold" :class="logoMode === 'url' ? 'bg-[#0E9F6E] text-white' : 'text-gray-600'">Pakai URL</button>
                </div>
            </div>
            <div x-show="logoMode === 'upload'">
                <input type="file" name="logo_file" accept="image/*" class="w-full text-xs bg-white p-2 border border-gray-200 rounded-xl file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-[#0E9F6E]">
            </div>
            <div x-show="logoMode === 'url'" x-cloak>
                <input type="url" name="logo_url" value="{{ old('logo_url', str_starts_with($store->logo, 'http') ? $store->logo : '') }}" placeholder="https://..." class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs">
            </div>
        </div>

        <!-- Banner Image Picker -->
        <div class="space-y-2 bg-[#FAF8F2] p-4 rounded-2xl border border-gray-200">
            <div class="flex items-center justify-between">
                <label class="block font-bold text-gray-700 uppercase">Banner Sampul Toko</label>
                <div class="flex items-center gap-1 bg-white p-0.5 rounded-lg border border-gray-200 text-xs">
                    <button type="button" @click="bannerMode = 'upload'" class="px-2.5 py-0.5 rounded-md font-semibold" :class="bannerMode === 'upload' ? 'bg-[#0E9F6E] text-white' : 'text-gray-600'">Upload File</button>
                    <button type="button" @click="bannerMode = 'url'" class="px-2.5 py-0.5 rounded-md font-semibold" :class="bannerMode === 'url' ? 'bg-[#0E9F6E] text-white' : 'text-gray-600'">Pakai URL</button>
                </div>
            </div>
            <div x-show="bannerMode === 'upload'">
                <input type="file" name="banner_file" accept="image/*" class="w-full text-xs bg-white p-2 border border-gray-200 rounded-xl file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-[#0E9F6E]">
            </div>
            <div x-show="bannerMode === 'url'" x-cloak>
                <input type="url" name="banner_url" value="{{ old('banner_url', str_starts_with($store->banner, 'http') ? $store->banner : '') }}" placeholder="https://..." class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs">
            </div>
        </div>

        <div>
            <label class="block font-bold text-gray-700 uppercase mb-1">Deskripsi Toko</label>
            <textarea name="description" rows="3" class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">{{ old('description', $store->description) }}</textarea>
        </div>

        <div class="pt-2">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_open" value="1" {{ $store->is_open ? 'checked' : '' }} class="rounded text-[#0E9F6E] focus:ring-[#0E9F6E]">
                <span class="font-bold text-gray-800 text-xs">Toko Buka & Menerima Pesanan Sekarang</span>
            </label>
        </div>

        <div class="pt-4 border-t border-gray-100">
            <button type="submit" class="w-full bg-[#0E9F6E] hover:bg-[#086644] text-white font-bold py-3 rounded-2xl shadow-md shadow-[#0E9F6E]/20 transition">
                Simpan Perubahan
            </button>
        </div>

    </form>

</div>
@endsection
