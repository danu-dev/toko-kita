@extends('layouts.app')

@section('title', $product->name . ' — ' . $product->store->name)

@section('content')
<div class="space-y-8" x-data="{ 
    selectedVariantPrice: 0, 
    basePrice: {{ $product->price }},
    quantity: 1,
    stock: {{ $product->stock }},
    get totalPrice() {
        return (this.basePrice + this.selectedVariantPrice) * this.quantity;
    }
}">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-gray-500">
        <a href="{{ route('home') }}" class="hover:text-[#0E9F6E]">Beranda</a>
        <i data-lucide="chevron-right" class="w-3 h-3 text-gray-400"></i>
        <a href="{{ route('explore', ['category' => $product->category_id]) }}" class="hover:text-[#0E9F6E]">{{ $product->category->name ?? 'Produk' }}</a>
        <i data-lucide="chevron-right" class="w-3 h-3 text-gray-400"></i>
        <a href="{{ route('stores.show', $product->store->slug) }}" class="hover:text-[#0E9F6E]">{{ $product->store->name }}</a>
        <i data-lucide="chevron-right" class="w-3 h-3 text-gray-400"></i>
        <span class="text-gray-800 font-semibold truncate">{{ $product->name }}</span>
    </nav>

    <!-- Product Detail Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left: Product Image -->
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white p-3 rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="aspect-square w-full rounded-2xl overflow-hidden bg-gray-50 relative">
                    <img src="{{ $product->image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80' }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                    @if($product->compare_at_price)
                        <span class="absolute top-3 left-3 bg-[#F2A93B] text-[#1E2723] text-xs font-black px-2.5 py-1 rounded-lg shadow-sm">
                            HEMAT Rp {{ number_format($product->compare_at_price - $product->price, 0, ',', '.') }}
                        </span>
                    @endif
                    @if($product->stock <= 0)
                        <div class="absolute inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center">
                            <span class="bg-red-600 text-white font-bold px-4 py-2 rounded-xl text-sm shadow-md">STOK HABIS</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Store Quick Box -->
            <a href="{{ route('stores.show', $product->store->slug) }}" class="bg-white p-4 rounded-2xl border border-gray-100 hover:border-[#0E9F6E] shadow-sm flex items-center justify-between transition group">
                <div class="flex items-center gap-3">
                    <img src="{{ $product->store->logo ?: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=100&q=80' }}" class="w-12 h-12 rounded-xl object-cover border border-gray-100">
                    <div>
                        <span class="text-[10px] text-[#0E9F6E] font-bold uppercase">Mitra UMKM</span>
                        <h4 class="font-bold text-sm text-[#1E2723] group-hover:text-[#0E9F6E] transition">{{ $product->store->name }}</h4>
                        <p class="text-xs text-gray-500">{{ $product->store->city }} • {{ $product->store->operational_hours }}</p>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="w-5 h-5 text-gray-400 group-hover:text-[#0E9F6E] transition"></i>
            </a>
        </div>

        <!-- Right: Information & Purchase Form -->
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
                <div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="bg-[#EDFDF5] text-[#0E9F6E] text-xs font-bold px-3 py-1 rounded-full">
                            {{ $product->category->name ?? 'Produk Unggulan' }}
                        </span>

                        @auth
                            <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="p-2 rounded-full border border-gray-200 hover:bg-red-50 text-gray-400 hover:text-red-500 transition">
                                    <i data-lucide="heart" class="w-5 h-5 {{ $isWishlisted ? 'fill-red-500 text-red-500' : '' }}"></i>
                                </button>
                            </form>
                        @endauth
                    </div>

                    <h1 class="font-display font-black text-2xl sm:text-3xl text-[#1E2723] mt-2">
                        {{ $product->name }}
                    </h1>

                    <div class="flex items-center gap-4 mt-2 text-xs">
                        <span class="text-[#F2A93B] font-bold flex items-center gap-1">
                            <i data-lucide="star" class="w-4 h-4 fill-current"></i> {{ number_format($product->rating, 1) }}
                        </span>
                        <span class="text-gray-300">|</span>
                        <span class="text-gray-500 font-mono">{{ $product->total_sales }} Terjual</span>
                        <span class="text-gray-300">|</span>
                        @if($product->stock > 0)
                            <span class="text-[#0E9F6E] font-semibold">Tersedia {{ $product->stock }} {{ $product->unit }}</span>
                        @else
                            <span class="text-red-500 font-bold">Stok Habis</span>
                        @endif
                    </div>

                    <div class="flex items-baseline gap-3 mt-4">
                        <span class="font-mono font-black text-3xl text-[#0B5A45]">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </span>
                        @if($product->compare_at_price)
                            <span class="text-base text-gray-400 line-through font-mono">
                                Rp {{ number_format($product->compare_at_price, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Deskripsi Produk</h3>
                    <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $product->description }}</p>
                </div>

                <!-- Own store alert or purchase form -->
                @if(Auth::check() && Auth::user()->store && Auth::user()->store->id === $product->store_id)
                    <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 text-amber-900 text-xs flex items-center gap-3">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-amber-600 shrink-0"></i>
                        <div>
                            <span class="font-bold block">Ini adalah produk dari toko Anda sendiri.</span>
                            <p class="text-amber-700 mt-0.5">Penjual tidak diperkenankan membeli produk miliknya sendiri. Anda dapat mengedit harga & stok di Dashboard Toko.</p>
                            <a href="{{ route('seller.products.edit', $product->id) }}" class="inline-block mt-2 font-bold text-[#0E9F6E] underline">Edit Produk Ini di Portal Seller &rarr;</a>
                        </div>
                    </div>
                @else
                    <!-- Purchase / Add to Cart Form -->
                    <form action="{{ route('cart.add') }}" method="POST" class="border-t border-gray-100 pt-6 space-y-5">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        @if($product->variants->isNotEmpty())
                            <div>
                                <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider mb-2">Pilih Varian / Porsi</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    @foreach($product->variants as $var)
                                        <label class="flex items-center justify-between p-3 rounded-2xl border border-gray-200 cursor-pointer hover:border-[#0E9F6E] transition has-[:checked]:border-[#0E9F6E] has-[:checked]:bg-[#EDFDF5]">
                                            <div class="flex items-center gap-2">
                                                <input type="radio" name="product_variant_id" value="{{ $var->id }}" @change="selectedVariantPrice = {{ $var->price_modifier }}" class="text-[#0E9F6E] focus:ring-[#0E9F6E]">
                                                <span class="text-xs font-semibold text-gray-800">{{ $var->name }}</span>
                                            </div>
                                            <span class="font-mono text-xs font-bold text-[#0B5A45]">
                                                + Rp {{ number_format($var->price_modifier, 0, ',', '.') }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider mb-2">Catatan untuk Penjual (Opsional)</label>
                            <input type="text" name="notes" placeholder="Contoh: jangan terlalu pedas, kuah dipisah..." class="w-full px-4 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm focus:border-[#0E9F6E]">
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-100">
                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <span class="text-xs font-bold text-gray-600">Jumlah:</span>
                                <div class="flex items-center border border-gray-200 rounded-xl bg-[#FAF8F2] p-1">
                                    <button type="button" @click="if(quantity > 1) quantity--" class="w-8 h-8 rounded-lg bg-white shadow-xs flex items-center justify-center text-gray-700 font-bold hover:bg-gray-100">-</button>
                                    <input type="number" name="quantity" x-model.number="quantity" min="1" max="{{ $product->stock }}" class="w-12 text-center bg-transparent border-0 font-mono font-bold text-sm focus:ring-0">
                                    <button type="button" @click="if(quantity < stock) quantity++" class="w-8 h-8 rounded-lg bg-white shadow-xs flex items-center justify-center text-gray-700 font-bold hover:bg-gray-100">+</button>
                                </div>
                            </div>

                            <div class="w-full sm:w-auto flex-1 flex items-center gap-3 justify-end">
                                <div class="text-right hidden sm:block">
                                    <span class="text-[10px] text-gray-400 uppercase font-bold block">Total Belanja</span>
                                    <span class="font-mono font-black text-xl text-[#0B5A45]" x-text="'Rp ' + (totalPrice).toLocaleString('id-ID')"></span>
                                </div>
                                <button type="submit" {{ $product->stock <= 0 ? 'disabled' : '' }} class="w-full sm:w-auto bg-[#0E9F6E] disabled:bg-gray-300 disabled:cursor-not-allowed hover:bg-[#086644] text-white font-bold px-8 py-3.5 rounded-2xl shadow-md shadow-[#0E9F6E]/20 transition flex items-center justify-center gap-2">
                                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                    <span>{{ $product->stock > 0 ? '+ Keranjang' : 'Stok Habis' }}</span>
                                </button>
                            </div>
                        </div>
                    </form>
                @endif

            </div>
        </div>

    </div>

</div>
@endsection
