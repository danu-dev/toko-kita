@extends('layouts.seller')

@section('title', 'Edit Produk — ' . $product->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('seller.products') }}" class="p-2 rounded-full bg-white border border-gray-200 hover:bg-gray-50">
            <i data-lucide="arrow-left" class="w-4 h-4 text-gray-700"></i>
        </a>
        <div>
            <h1 class="font-display font-black text-2xl text-[#0B5A45]">Edit Produk</h1>
            <p class="text-xs text-gray-500">Perbarui harga, stok, atau foto produk toko Anda.</p>
        </div>
    </div>

    <form action="{{ route('seller.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-100 shadow-sm space-y-4" x-data="{ imageMode: 'upload' }">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Nama Produk / Makanan</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Kategori</label>
                <select name="category_id" required class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ $product->category_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Satuan</label>
                <input type="text" name="unit" value="{{ old('unit', $product->unit) }}" required class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Harga Jual (Rp)</label>
                <input type="number" name="price" value="{{ old('price', (int)$product->price) }}" required class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Harga Coret (Opsional)</label>
                <input type="number" name="compare_at_price" value="{{ old('compare_at_price', (int)$product->compare_at_price) }}" class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Stok</label>
                <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" required class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
            </div>

            <!-- Image Selection with Dual Option (Upload File or URL) -->
            <div class="sm:col-span-2 space-y-2 bg-[#FAF8F2] p-4 rounded-2xl border border-gray-200">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold uppercase text-gray-700">Foto Produk</label>
                    <div class="flex items-center gap-1 bg-white p-0.5 rounded-lg border border-gray-200 text-xs">
                        <button type="button" @click="imageMode = 'upload'" class="px-2.5 py-0.5 rounded-md font-semibold transition" :class="imageMode === 'upload' ? 'bg-[#0E9F6E] text-white' : 'text-gray-600'">Upload File</button>
                        <button type="button" @click="imageMode = 'url'" class="px-2.5 py-0.5 rounded-md font-semibold transition" :class="imageMode === 'url' ? 'bg-[#0E9F6E] text-white' : 'text-gray-600'">Pakai URL</button>
                    </div>
                </div>

                @if($product->image)
                    <div class="flex items-center gap-3 p-2 bg-white rounded-xl border border-gray-100">
                        <img src="{{ $product->image }}" class="w-12 h-12 rounded-lg object-cover">
                        <span class="text-[11px] text-gray-500 truncate max-w-xs">Foto saat ini terpasang</span>
                    </div>
                @endif

                <div x-show="imageMode === 'upload'">
                    <input type="file" name="image_file" accept="image/*" class="w-full text-xs bg-white p-2 border border-gray-200 rounded-xl file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-[#0E9F6E]">
                </div>

                <div x-show="imageMode === 'url'" x-cloak>
                    <input type="url" name="image_url" value="{{ old('image_url', str_starts_with($product->image, 'http') ? $product->image : '') }}" placeholder="https://images.unsplash.com/..." class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs">
                </div>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Deskripsi Lengkap</label>
                <textarea name="description" rows="3" required class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="sm:col-span-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }} class="rounded text-[#0E9F6E] focus:ring-[#0E9F6E]">
                    <span class="text-xs font-bold text-gray-800">Tampilkan Produk di Marketplace (Status Aktif)</span>
                </label>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100">
            <button type="submit" class="w-full bg-[#0E9F6E] hover:bg-[#086644] text-white font-bold py-3.5 rounded-2xl shadow-md shadow-[#0E9F6E]/20 transition text-sm">
                Perbarui Data Produk
            </button>
        </div>

    </form>

</div>
@endsection
