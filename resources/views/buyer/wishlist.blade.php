@extends('layouts.app')

@section('title', 'Wishlist Produk Favorit — Toko Kita')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display font-black text-2xl text-[#0B5A45]">Wishlist Favorit</h1>
            <p class="text-xs text-gray-500">Daftar produk UMKM yang kamu simpan untuk dibeli nanti.</p>
        </div>
    </div>

    @if($wishlists->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-gray-100 shadow-sm space-y-4">
            <i data-lucide="heart" class="w-12 h-12 text-gray-300 mx-auto"></i>
            <h3 class="font-bold text-gray-700">Wishlist masih kosong</h3>
            <a href="{{ route('explore') }}" class="inline-block bg-[#0E9F6E] text-white px-5 py-2 rounded-xl text-xs font-bold">Jelajah Produk</a>
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
@endsection
