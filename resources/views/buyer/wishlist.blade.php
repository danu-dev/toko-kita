@extends('layouts.app')

@section('title', 'Wishlist & Toko Favorit — Toko Kita')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display font-black text-2xl text-[#0B5A45]">Wishlist & Toko Favorit</h1>
            <p class="text-xs text-gray-500">Kelola daftar produk dan toko UMKM langganan favorit Anda.</p>
        </div>
    </div>

    <!-- Tab Section: Toko Favorit -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="font-display font-bold text-base text-[#0B5A45] flex items-center gap-2">
                <i data-lucide="store" class="w-4 h-4 text-[#0E9F6E]"></i>
                <span>Toko UMKM Favorit ({{ $favoriteStores->count() }})</span>
            </h2>
        </div>

        @if($favoriteStores->isEmpty())
            <div class="bg-white rounded-3xl p-8 text-center border border-gray-100 shadow-sm space-y-2">
                <i data-lucide="store" class="w-10 h-10 text-gray-300 mx-auto"></i>
                <p class="text-xs text-gray-500 font-medium">Belum ada toko yang difavoritkan. Jelajahi toko lokal terbaik dan klik Favoritkan!</p>
                <a href="{{ route('explore') }}" class="inline-block bg-[#EDFDF5] text-[#0E9F6E] hover:bg-[#0E9F6E] hover:text-white px-4 py-2 rounded-xl text-xs font-bold transition">Cari Toko UMKM</a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($favoriteStores as $fav)
                    <div class="bg-white rounded-2xl border border-gray-100 p-4 hover:border-[#0E9F6E] hover:shadow-sm transition flex flex-col justify-between">
                        <div class="flex items-start gap-3">
                            <img src="{{ $fav->store->logo ?: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=150&q=80' }}" class="w-12 h-12 rounded-xl object-cover border border-gray-100 bg-gray-50">
                            <div class="flex-1 min-w-0">
                                <span class="text-[10px] font-bold text-[#0E9F6E] uppercase tracking-wider">{{ $fav->store->category->name ?? 'UMKM' }}</span>
                                <h3 class="font-bold text-sm text-[#1E2723] truncate">{{ $fav->store->name }}</h3>
                                <p class="text-[11px] text-gray-500 truncate flex items-center gap-1 mt-0.5">
                                    <i data-lucide="map-pin" class="w-3 h-3 text-gray-400"></i>
                                    <span>{{ $fav->store->city }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-3 mt-3 border-t border-gray-100 text-xs">
                            <div class="flex items-center gap-1 text-[#F2A93B] font-bold">
                                <i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>
                                <span>{{ number_format($fav->store->rating, 1) }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <form action="{{ route('stores.favorite.toggle', $fav->store->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" title="Hapus dari favorit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition">
                                        <i data-lucide="heart-off" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                <a href="{{ route('stores.show', $fav->store->slug) }}" class="bg-[#FAF8F2] hover:bg-[#0E9F6E] hover:text-white px-3 py-1.5 rounded-lg text-xs font-bold text-[#0B5A45] transition">Kunjungi Toko</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Section: Produk Favorit (Wishlist) -->
    <div class="space-y-4 pt-4 border-t border-gray-200/60">
        <div class="flex items-center justify-between">
            <h2 class="font-display font-bold text-base text-[#0B5A45] flex items-center gap-2">
                <i data-lucide="heart" class="w-4 h-4 text-rose-500"></i>
                <span>Produk Wishlist ({{ $wishlists->count() }})</span>
            </h2>
        </div>

        @if($wishlists->isEmpty())
            <div class="bg-white rounded-3xl p-8 text-center border border-gray-100 shadow-sm space-y-2">
                <i data-lucide="heart" class="w-10 h-10 text-gray-300 mx-auto"></i>
                <p class="text-xs text-gray-500 font-medium">Wishlist produk masih kosong.</p>
                <a href="{{ route('explore') }}" class="inline-block bg-[#0E9F6E] text-white px-4 py-2 rounded-xl text-xs font-bold">Jelajah Produk</a>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($wishlists as $w)
                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden p-3.5 space-y-2 relative">
                        <img src="{{ $w->product->image }}" class="w-full aspect-square rounded-xl object-cover">
                        <h4 class="font-bold text-xs text-gray-800 line-clamp-1">{{ $w->product->name }}</h4>
                        <p class="font-mono font-bold text-xs text-[#0B5A45]">Rp {{ number_format($w->product->price, 0, ',', '.') }}</p>
                        <div class="flex items-center gap-2 pt-2">
                            <a href="{{ route('products.show', $w->product->slug) }}" class="flex-1 bg-[#FAF8F2] hover:bg-[#0E9F6E] hover:text-white py-1.5 rounded-lg text-[11px] font-bold text-center transition">Lihat</a>
                            <form action="{{ route('wishlist.toggle', $w->product->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
