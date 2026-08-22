@extends('layouts.app')

@section('title', 'Jelajah Produk & Toko UMKM — Toko Kita')

@section('content')
<div class="space-y-6">

    <!-- Search & Filter Header -->
    <div class="bg-white p-4 sm:p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
        <form action="{{ route('explore') }}" method="GET" class="space-y-3">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <input type="text" name="q" value="{{ $query }}" placeholder="Cari masakan, snack, sembako, kerajinan..." class="w-full pl-10 pr-4 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-2xl text-sm focus:border-[#0E9F6E] focus:outline-none">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3.5 top-3.5"></i>
                </div>
                <select name="category" class="px-4 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-2xl text-sm focus:border-[#0E9F6E] focus:outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <select name="sort" class="px-4 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-2xl text-sm focus:border-[#0E9F6E] focus:outline-none">
                    <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Terlaris</option>
                    <option value="rating" {{ $sort === 'rating' ? 'selected' : '' }}>Rating Tertinggi</option>
                    <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Harga: Rendah ke Tinggi</option>
                    <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Harga: Tinggi ke Rendah</option>
                </select>
                <button type="submit" class="bg-[#0E9F6E] hover:bg-[#086644] text-white px-6 py-2.5 rounded-2xl font-bold text-sm shadow-sm transition">
                    Terapkan
                </button>
            </div>

            <!-- Price Range & Promo Quick Filter Bar -->
            <div class="flex flex-wrap items-center gap-3 pt-1 text-xs">
                <div class="flex items-center gap-2">
                    <span class="text-gray-500 font-semibold">Rentang Harga:</span>
                    <input type="number" name="min_price" value="{{ $minPrice }}" placeholder="Min (Rp)" class="w-28 px-3 py-1.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-xs focus:border-[#0E9F6E] focus:outline-none">
                    <span class="text-gray-400">-</span>
                    <input type="number" name="max_price" value="{{ $maxPrice }}" placeholder="Maks (Rp)" class="w-28 px-3 py-1.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-xs focus:border-[#0E9F6E] focus:outline-none">
                </div>

                <label class="inline-flex items-center gap-2 cursor-pointer bg-[#FAF8F2] hover:bg-[#F2EFE9] px-3 py-1.5 rounded-xl border border-gray-200 select-none">
                    <input type="checkbox" name="promo" value="1" {{ $onlyPromo ? 'checked' : '' }} class="rounded text-[#0E9F6E] focus:ring-[#0E9F6E]">
                    <span class="font-bold text-[#1E2723] flex items-center gap-1">
                        <i data-lucide="tag" class="w-3.5 h-3.5 text-[#F2A93B]"></i> Hanya Promo
                    </span>
                </label>

                @if($query || $categoryId || $minPrice || $maxPrice || $onlyPromo || ($sort && $sort !== 'latest'))
                    <a href="{{ route('explore') }}" class="text-red-500 hover:underline font-semibold ml-auto">
                        Reset Filter
                    </a>
                @endif
            </div>
        </form>

        <!-- Category Pills Bar -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <a href="{{ route('explore', array_merge(request()->except(['category', 'page']))) }}" class="px-3.5 py-1.5 rounded-full whitespace-nowrap font-semibold {{ empty($categoryId) ? 'bg-[#0E9F6E] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Semua
            </a>
            @foreach($categories as $c)
                <a href="{{ route('explore', array_merge(request()->except(['category', 'page']), ['category' => $c->id])) }}" class="px-3.5 py-1.5 rounded-full whitespace-nowrap font-semibold {{ $categoryId == $c->id ? 'bg-[#0E9F6E] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ $c->name }}
                </a>
            @endforeach
        </div>
    </div>

    @if($stores->isNotEmpty() && empty($categoryId))
        <div class="space-y-3">
            <h2 class="font-display font-bold text-base text-[#0B5A45]">Toko Terkait</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach($stores as $st)
                    <a href="{{ route('stores.show', $st->slug) }}" class="bg-white p-3 rounded-2xl border border-gray-100 hover:border-[#0E9F6E] flex items-center gap-3 transition">
                        <img src="{{ $st->logo ?: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=100&q=80' }}" class="w-12 h-12 rounded-xl object-cover">
                        <div class="min-w-0">
                            <h4 class="font-bold text-xs text-[#1E2723] truncate">{{ $st->name }}</h4>
                            <p class="text-[11px] text-[#F2A93B] font-semibold flex items-center gap-1 mt-0.5">
                                <i data-lucide="star" class="w-3 h-3 fill-current"></i> {{ number_format($st->rating, 1) }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Product Grid -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="font-display font-bold text-lg text-[#0B5A45]">
                {{ $products->total() }} Produk Ditemukan
            </h2>
        </div>

        @if($products->isEmpty())
            <div class="text-center py-16 bg-white rounded-3xl border border-gray-100">
                <i data-lucide="package-search" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                <h3 class="font-bold text-gray-700">Produk tidak ditemukan</h3>
                <p class="text-xs text-gray-400 mt-1">Coba gunakan kata kunci pencarian lain atau pilih kategori berbeda.</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($products as $product)
                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:border-[#0E9F6E] hover:shadow-md transition flex flex-col group">
                        <a href="{{ route('products.show', $product->slug) }}" class="relative aspect-square w-full bg-gray-50 overflow-hidden block">
                            <img src="{{ $product->image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80' }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @if($product->compare_at_price)
                                <span class="absolute top-2 left-2 bg-[#F2A93B] text-[#1E2723] text-[10px] font-black px-2 py-0.5 rounded-md shadow-xs">
                                    PROMO
                                </span>
                            @endif
                        </a>

                        <div class="p-3.5 flex-1 flex flex-col justify-between space-y-2">
                            <div>
                                <p class="text-[11px] text-gray-500 truncate flex items-center gap-1">
                                    <i data-lucide="store" class="w-3 h-3 text-gray-400"></i>
                                    <span>{{ $product->store->name }}</span>
                                </p>
                                <a href="{{ route('products.show', $product->slug) }}" class="font-display font-bold text-sm text-[#1E2723] group-hover:text-[#0E9F6E] line-clamp-2 mt-0.5 leading-snug">
                                    {{ $product->name }}
                                </a>
                            </div>

                            <div>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="font-mono font-bold text-base text-[#0B5A45]">
                                        Rp {{ number_format($product->price, 0, ',', '.') }}
                                    </span>
                                    @if($product->compare_at_price)
                                        <span class="text-[11px] text-gray-400 line-through font-mono">
                                            Rp {{ number_format($product->compare_at_price, 0, ',', '.') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-50 text-[11px]">
                                    <span class="text-gray-500 flex items-center gap-0.5">
                                        <i data-lucide="star" class="w-3 h-3 text-[#F2A93B] fill-[#F2A93B]"></i>
                                        <b>{{ number_format($product->rating, 1) }}</b>
                                    </span>
                                    <span class="text-gray-400 font-mono">{{ $product->total_sales }} terjual</span>
                                </div>
                            </div>

                            <form action="{{ route('cart.add') }}" method="POST" class="pt-1">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="w-full bg-[#FAF8F2] hover:bg-[#0E9F6E] text-[#0B5A45] hover:text-white font-bold py-2 rounded-xl text-xs flex items-center justify-center gap-1.5 transition">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <span>Tambah</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
