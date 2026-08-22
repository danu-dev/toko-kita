@extends('layouts.admin')

@section('title', 'Master Data Kategori — Admin Toko Kita')

@section('content')
<div class="max-w-4xl space-y-6">

    <div>
        <h1 class="font-display font-black text-2xl text-[#0B5A45]">Master Kategori Produk UMKM</h1>
        <p class="text-xs text-gray-500">Kelola taksonomi kategori untuk mengelompokkan makanan, barang, dan kriya lokal.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        
        <!-- Add Category Form -->
        <div class="md:col-span-5 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
            <h3 class="font-display font-bold text-base text-[#0B5A45]">+ Tambah Kategori Baru</h3>
            <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Nama Kategori</label>
                    <input type="text" name="name" required placeholder="Contoh: Fashion & Batik" class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Ikon (Lucide tag)</label>
                    <input type="text" name="icon" placeholder="tag, shirt, utensils..." class="w-full px-3.5 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
                </div>
                <button type="submit" class="w-full bg-[#0E9F6E] hover:bg-[#086644] text-white font-bold py-3 rounded-2xl shadow-md transition">
                    Simpan Kategori
                </button>
            </form>
        </div>

        <!-- Categories List -->
        <div class="md:col-span-7 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
            <h3 class="font-display font-bold text-base text-[#0B5A45]">Daftar Kategori Aktif</h3>
            <div class="divide-y divide-gray-100">
                @foreach($categories as $cat)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-[#0E9F6E] flex items-center justify-center">
                                <i data-lucide="tag" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <span class="font-bold text-gray-800 text-sm block">{{ $cat->name }}</span>
                                <span class="text-gray-400 text-[11px]">{{ $cat->products_count }} Produk • {{ $cat->stores_count }} Toko</span>
                            </div>
                        </div>

                        <form action="{{ route('admin.categories.delete', $cat->id) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-xl transition">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

</div>
@endsection
