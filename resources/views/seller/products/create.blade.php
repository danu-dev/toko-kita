@extends('layouts.seller')

@section('title', 'Tambah Produk Baru — ' . $store->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('seller.products') }}" class="p-2 rounded-full bg-white border border-gray-200 hover:bg-gray-50">
            <i data-lucide="arrow-left" class="w-4 h-4 text-gray-700"></i>
        </a>
        <div>
            <h1 class="font-display font-black text-2xl text-[#0B5A45]">Tambah Produk Baru</h1>
            <p class="text-xs text-gray-500">Lengkapi detail menu atau barang dagangan toko Anda.</p>
        </div>
    </div>

    <form action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-100 shadow-sm space-y-4" x-data="{ variants: [], imageMode: 'upload' }">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Nama Produk / Makanan</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Nasi Goreng Spesial" class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Kategori</label>
                <select name="category_id" required class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Satuan</label>
                <input type="text" name="unit" value="{{ old('unit', 'porsi') }}" placeholder="porsi, pcs, cup, box, kg" required class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Harga Jual (Rp)</label>
                <input type="number" name="price" value="{{ old('price') }}" required placeholder="25000" class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Harga Coret / Promo (Opsional)</label>
                <input type="number" name="compare_at_price" value="{{ old('compare_at_price') }}" placeholder="30000" class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Stok Awal</label>
                <input type="number" name="stock" value="{{ old('stock', 50) }}" required class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
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

                <div x-show="imageMode === 'upload'">
                    <input type="file" name="image_file" accept="image/*" class="w-full text-xs bg-white p-2 border border-gray-200 rounded-xl file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-[#0E9F6E]">
                </div>

                <div x-show="imageMode === 'url'" x-cloak>
                    <input type="url" name="image_url" placeholder="https://images.unsplash.com/..." class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs">
                </div>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Deskripsi Lengkap Produk</label>
                <textarea name="description" rows="3" required placeholder="Jelaskan bahan, rasa, dan keunggulan produk Anda..." class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">{{ old('description') }}</textarea>
            </div>
        </div>

        <!-- Variants Section -->
        <div class="pt-4 border-t border-gray-100 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-xs text-[#0B5A45] uppercase tracking-wider">Varian Produk (Opsional)</h3>
                    <p class="text-[11px] text-gray-400">Contoh: Level Pedas, Ekstra Telur, Ukuran Jumbo</p>
                </div>
                <button type="button" @click="variants.push({ name: '', price: 0, stock: 50 })" class="bg-[#EDFDF5] text-[#0E9F6E] font-bold px-3 py-1.5 rounded-xl text-xs flex items-center gap-1 hover:bg-[#0E9F6E] hover:text-white transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah Varian</span>
                </button>
            </div>

            <template x-for="(v, index) in variants" :key="index">
                <div class="flex items-center gap-2 bg-[#FAF8F2] p-2.5 rounded-2xl border border-gray-200">
                    <input type="text" name="variant_name[]" placeholder="Nama varian" x-model="v.name" required class="flex-1 px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs">
                    <input type="number" name="variant_price[]" placeholder="+Harga (Rp)" x-model="v.price" required class="w-28 px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs">
                    <button type="button" @click="variants.splice(index, 1)" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </template>
        </div>

        <div class="pt-4 border-t border-gray-100">
            <button type="submit" class="w-full bg-[#0E9F6E] hover:bg-[#086644] text-white font-bold py-3.5 rounded-2xl shadow-md shadow-[#0E9F6E]/20 transition text-sm">
                Simpan & Terbitkan Produk
            </button>
        </div>

    </form>

</div>
@endsection
