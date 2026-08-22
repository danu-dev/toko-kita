@extends('layouts.app')

@section('title', $store->name . ' — Toko Kita')

@section('content')
<div class="space-y-6">

    <!-- Store Header Card -->
    <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm">
        <div class="h-44 sm:h-56 w-full bg-[#0B5A45] relative overflow-hidden">
            <img src="{{ $store->banner ?: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80' }}" class="w-full h-full object-cover opacity-80">
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
        </div>
        
        <div class="p-6 sm:p-8 -mt-16 sm:-mt-20 relative z-10">
            <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between gap-4">
                <div class="flex items-end gap-4">
                    <img src="{{ $store->logo ?: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=200&q=80' }}" class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl object-cover border-4 border-white shadow-md bg-white">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="bg-[#EDFDF5] text-[#0E9F6E] text-[10px] font-bold px-2 py-0.5 rounded-md">MITRA RESMI</span>
                            <span class="text-xs text-gray-500">{{ $store->category->name ?? 'UMKM' }}</span>
                        </div>
                        <h1 class="font-display font-black text-2xl sm:text-3xl text-[#1E2723]">{{ $store->name }}</h1>
                    </div>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    @auth
                        @if(!Auth::user()->store || Auth::user()->store->id !== $store->id)
                            <form action="{{ route('stores.favorite.toggle', $store->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2.5 rounded-2xl font-bold text-xs border flex items-center gap-1.5 transition {{ $isFavorited ? 'bg-rose-50 border-rose-200 text-rose-600 hover:bg-rose-100' : 'bg-[#FAF8F2] border-gray-200 text-gray-700 hover:text-rose-600 hover:border-rose-200' }}">
                                    <i data-lucide="heart" class="w-4 h-4 {{ $isFavorited ? 'fill-current text-rose-500' : '' }}"></i>
                                    <span>{{ $isFavorited ? 'Mengikuti' : 'Favoritkan' }}</span>
                                    @if($totalFollowers > 0)
                                        <span class="ml-0.5 text-[11px] opacity-80">({{ $totalFollowers }})</span>
                                    @endif
                                </button>
                            </form>

                            <form action="{{ route('chats.start') }}" method="POST">
                                @csrf
                                <input type="hidden" name="store_id" value="{{ $store->id }}">
                                <button type="submit" class="bg-[#FAF8F2] hover:bg-[#0E9F6E] text-[#0B5A45] hover:text-white px-4 py-2.5 rounded-2xl font-bold text-xs border border-gray-200 flex items-center gap-1.5 transition">
                                    <i data-lucide="message-square" class="w-4 h-4"></i>
                                    <span>Chat Penjual</span>
                                </button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="bg-[#FAF8F2] hover:bg-rose-50 text-gray-700 hover:text-rose-600 px-4 py-2.5 rounded-2xl font-bold text-xs border border-gray-200 flex items-center gap-1.5 transition">
                            <i data-lucide="heart" class="w-4 h-4"></i>
                            <span>Favoritkan</span>
                            @if($totalFollowers > 0)
                                <span class="ml-0.5 text-[11px] opacity-80">({{ $totalFollowers }})</span>
                            @endif
                        </a>
                    @endauth

                    <!-- Share Store Button -->
                    <div x-data="{
                        copied: false,
                        shareStore() {
                            if (navigator.share) {
                                navigator.share({
                                    title: '{{ addslashes($store->name) }}',
                                    text: 'Kunjungi toko UMKM {{ addslashes($store->name) }} di TokoKita!',
                                    url: window.location.href
                                }).catch(() => {});
                            } else {
                                navigator.clipboard.writeText(window.location.href);
                                this.copied = true;
                                setTimeout(() => this.copied = false, 2000);
                            }
                        }
                    }">
                        <button type="button" @click="shareStore()" class="p-2.5 rounded-2xl border border-gray-200 hover:bg-emerald-50 text-gray-500 hover:text-[#0E9F6E] transition relative" title="Bagikan Toko">
                            <i data-lucide="share-2" class="w-4 h-4"></i>
                            <span x-show="copied" x-cloak class="absolute -bottom-8 right-0 bg-[#1E2723] text-white text-[10px] py-1 px-2 rounded-md font-bold whitespace-nowrap shadow-md">
                                Tautan Disalin!
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <p class="text-xs sm:text-sm text-gray-600 mt-4 max-w-3xl leading-relaxed">
                {{ $store->description }}
            </p>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-6 border-t border-gray-100 text-xs">
                <div>
                    <span class="text-gray-400 block text-[11px]">Rating Toko</span>
                    <span class="font-bold text-[#F2A93B] text-sm flex items-center gap-1 mt-0.5">
                        <i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i> {{ number_format($store->rating, 1) }} ({{ $store->total_reviews }} ulasan)
                    </span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">Jarak dari Anda</span>
                    <span class="font-bold text-[#0E9F6E] text-sm mt-0.5 flex items-center gap-1">
                        <i data-lucide="navigation" class="w-3.5 h-3.5 text-[#0E9F6E]"></i>
                        <span x-text="$store?.userLocation ? $store.userLocation.getDistanceTo({{ $store->latitude }}, {{ $store->longitude }}) : 'Menghitung...'">Menghitung...</span>
                    </span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">Jam Buka</span>
                    <span class="font-bold text-gray-800 text-sm mt-0.5 block">{{ $store->operational_hours }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">Status Operasional</span>
                    @if($store->is_open)
                        <span class="inline-flex items-center gap-1.5 text-[#0E9F6E] font-bold text-xs mt-0.5">
                            <span class="w-2 h-2 rounded-full bg-[#0E9F6E] animate-pulse"></span> Buka Sekarang
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-red-600 font-bold text-xs mt-0.5">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span> Tutup Sementara
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Store Map & Location Box -->
    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="font-display font-bold text-base text-[#0B5A45] flex items-center gap-2">
                <i data-lucide="map-pin" class="w-4 h-4 text-[#0E9F6E]"></i>
                <span>Titik Lokasi & Alamat Fisik Toko</span>
            </h3>
            <span class="text-xs text-gray-500 font-mono">{{ $store->address }}, {{ $store->city }}</span>
        </div>
        <div id="store-location-map" class="h-48 w-full rounded-2xl z-0"></div>
    </div>

    <!-- Products Catalog from this Store -->
    <div class="space-y-4">
        <h2 class="font-display font-bold text-lg text-[#0B5A45]">Menu & Produk Toko Ini</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse($store->products as $product)
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:border-[#0E9F6E] hover:shadow-md transition flex flex-col group">
                    <a href="{{ route('products.show', $product->slug) }}" class="relative aspect-square w-full bg-gray-50 overflow-hidden block">
                        <img src="{{ $product->image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80' }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @if($product->stock <= 0)
                            <span class="absolute inset-0 bg-black/60 text-white text-xs font-bold flex items-center justify-center">STOK HABIS</span>
                        @endif
                    </a>

                    <div class="p-3.5 flex-1 flex flex-col justify-between space-y-2">
                        <div>
                            <a href="{{ route('products.show', $product->slug) }}" class="font-display font-bold text-sm text-[#1E2723] group-hover:text-[#0E9F6E] line-clamp-2 leading-snug">
                                {{ $product->name }}
                            </a>
                            <p class="text-xs text-gray-500 line-clamp-1 mt-0.5">{{ $product->description }}</p>
                        </div>

                        <div>
                            <div class="font-mono font-bold text-base text-[#0B5A45]">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </div>
                            <span class="text-[11px] text-gray-400">Stok: {{ $product->stock }} {{ $product->unit }}</span>
                        </div>

                        @if(!Auth::check() || !Auth::user()->store || Auth::user()->store->id !== $product->store_id)
                            <form action="{{ route('cart.add') }}" method="POST" class="pt-1">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" {{ $product->stock <= 0 ? 'disabled' : '' }} class="w-full bg-[#FAF8F2] hover:bg-[#0E9F6E] text-[#0B5A45] hover:text-white disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed font-bold py-2 rounded-xl text-xs flex items-center justify-center gap-1.5 transition">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <span>{{ $product->stock > 0 ? 'Tambah' : 'Habis' }}</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-gray-400 bg-white rounded-2xl">
                    Belum ada produk aktif di toko ini.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Store Reviews Section -->
    @if($store->reviews->isNotEmpty())
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-100 shadow-sm space-y-4">
            <h3 class="font-display font-bold text-lg text-[#0B5A45]">Ulasan Pelanggan</h3>
            <div class="space-y-4 divide-y divide-gray-100">
                @foreach($store->reviews as $rev)
                    <div class="pt-4 first:pt-0 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-[#0E9F6E] font-bold flex items-center justify-center text-xs">
                                    {{ substr($rev->buyer->name ?? 'P', 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs text-gray-800">{{ $rev->buyer->name ?? 'Pelanggan' }}</h4>
                                    <span class="text-[10px] text-gray-400">{{ $rev->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 text-[#F2A93B]">
                                @for($i=1; $i<=5; $i++)
                                    <i data-lucide="star" class="w-3 h-3 {{ $i <= $rev->rating ? 'fill-current' : 'text-gray-200' }}"></i>
                                @endfor
                            </div>
                        </div>
                        <p class="text-xs text-gray-700">{{ $rev->comment }}</p>
                        @if($rev->seller_reply)
                            <div class="bg-[#FAF8F2] p-3 rounded-xl border-l-2 border-[#0E9F6E] ml-4 text-xs space-y-1">
                                <span class="font-bold text-[#0B5A45] block">Respon Penjual:</span>
                                <p class="text-gray-600">{{ $rev->seller_reply }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof L === 'undefined') return;
        const mapElement = document.getElementById('store-location-map');
        if (!mapElement) return;

        const storeLat = {{ $store->latitude }};
        const storeLng = {{ $store->longitude }};

        const map = L.map('store-location-map').setView([storeLat, storeLng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const storeIcon = L.divIcon({
            className: 'custom-store-pin',
            html: "<div class='w-8 h-8 rounded-2xl bg-[#0E9F6E] border-2 border-white shadow-md flex items-center justify-center text-white text-sm'>🏪</div>",
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });

        L.marker([storeLat, storeLng], { icon: storeIcon })
            .addTo(map)
            .bindPopup("<b>{{ $store->name }}</b><br>{{ $store->address }}")
            .openPopup();
    });
</script>
@endsection
