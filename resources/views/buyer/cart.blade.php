@extends('layouts.app')

@section('title', 'Keranjang Belanja — Toko Kita')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display font-black text-2xl sm:text-3xl text-[#0B5A45]">Keranjang Belanja</h1>
            <p class="text-xs text-gray-500 mt-0.5">Pesanan diproses per toko untuk menjaga kualitas dan kecepatan antar.</p>
        </div>
        <a href="{{ route('explore') }}" class="text-xs font-bold text-[#0E9F6E] hover:underline flex items-center gap-1">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
            <span>Tambah Produk Lain</span>
        </a>
    </div>

    @if($groupedCart->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-gray-100 shadow-sm space-y-4">
            <div class="w-16 h-16 rounded-3xl bg-[#FAF8F2] text-gray-400 mx-auto flex items-center justify-center">
                <i data-lucide="shopping-bag" class="w-8 h-8"></i>
            </div>
            <div>
                <h3 class="font-display font-bold text-lg text-gray-800">Keranjang Belanja Kosong</h3>
                <p class="text-xs text-gray-500 mt-1">Yuk jelajahi aneka kuliner dan produk dari mitra UMKM sekitar!</p>
            </div>
            <a href="{{ route('explore') }}" class="inline-block bg-[#0E9F6E] hover:bg-[#086644] text-white font-bold px-6 py-3 rounded-2xl text-xs shadow-md transition">
                Mulai Belanja Sekarang
            </a>
        </div>
    @else
        <!-- Multi-store Cart Loop (PRD Spec Section 3.1 & 6) -->
        <div class="space-y-6">
            @foreach($groupedCart as $storeId => $cartItems)
                @php
                    $store = $cartItems->first()->product->store;
                    $storeSubtotal = $cartItems->sum(fn($i) => $i->subtotal);
                @endphp
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    
                    <!-- Store Header Bar -->
                    <div class="bg-[#FAF8F2] p-4 sm:px-6 flex items-center justify-between border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <i data-lucide="store" class="w-5 h-5 text-[#0E9F6E]"></i>
                            <div>
                                <h3 class="font-bold text-sm text-[#1E2723]">{{ $store->name }}</h3>
                                <p class="text-[11px] text-gray-400">{{ $store->city }}</p>
                            </div>
                        </div>
                        <a href="{{ route('stores.show', $store->slug) }}" class="text-xs text-[#0E9F6E] font-semibold hover:underline">
                            Lihat Toko
                        </a>
                    </div>

                    <!-- Store Cart Items -->
                    <div class="p-4 sm:p-6 divide-y divide-gray-100 space-y-4">
                        @foreach($cartItems as $item)
                            <div class="pt-4 first:pt-0 flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3.5">
                                    <img src="{{ $item->product->image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=150&q=80' }}" class="w-16 h-16 rounded-xl object-cover border border-gray-100 shrink-0">
                                    <div>
                                        <h4 class="font-display font-bold text-sm text-[#1E2723]">{{ $item->product->name }}</h4>
                                        @if($item->variant)
                                            <span class="inline-block bg-[#EDFDF5] text-[#0E9F6E] text-[10px] font-semibold px-2 py-0.5 rounded mt-0.5">
                                                Varian: {{ $item->variant->name }}
                                            </span>
                                        @endif
                                        @if($item->notes)
                                            <p class="text-[11px] text-gray-400 italic mt-0.5">"{{ $item->notes }}"</p>
                                        @endif
                                        <p class="font-mono font-bold text-xs text-[#0B5A45] mt-1">
                                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-col items-end gap-2">
                                    <div class="flex items-center border border-gray-200 rounded-xl bg-[#FAF8F2] p-0.5">
                                        <form action="{{ route('cart.update', $item->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="decrease">
                                            <button type="submit" class="w-6 h-6 rounded-lg bg-white shadow-xs flex items-center justify-center text-xs text-gray-700 font-bold hover:bg-gray-100">-</button>
                                        </form>
                                        <span class="w-8 text-center font-mono font-bold text-xs">{{ $item->quantity }}</span>
                                        <form action="{{ route('cart.update', $item->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="increase">
                                            <button type="submit" class="w-6 h-6 rounded-lg bg-white shadow-xs flex items-center justify-center text-xs text-gray-700 font-bold hover:bg-gray-100">+</button>
                                        </form>
                                    </div>

                                    <form action="{{ route('cart.update', $item->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="text-[11px] text-red-500 hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Store Checkout CTA -->
                    <div class="bg-[#FAF8F2] p-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-gray-100">
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase font-bold block">Subtotal Toko</span>
                            <span class="font-mono font-bold text-lg text-[#0B5A45]">
                                Rp {{ number_format($storeSubtotal, 0, ',', '.') }}
                            </span>
                        </div>

                        <a href="{{ route('checkout', $store->id) }}" class="w-full sm:w-auto bg-[#0E9F6E] hover:bg-[#086644] text-white font-bold px-6 py-3 rounded-2xl text-xs shadow-md shadow-[#0E9F6E]/20 transition flex items-center justify-center gap-2">
                            <span>Checkout Toko Ini</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>

                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
