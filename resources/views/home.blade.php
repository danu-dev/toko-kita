@extends('layouts.app')

@section('title', 'Toko Kita — Belanja Nyaman dari UMKM Sekitar')

@section('content')
<div class="space-y-8">

    <!-- 1. Hero Promo Banner Carousel / Grid -->
    <section class="relative rounded-3xl overflow-hidden shadow-lg border border-[#0B5A45]/20 bg-[#0B5A45] text-white">
        <div class="grid grid-cols-1 lg:grid-cols-12 items-center">
            <div class="p-6 sm:p-10 lg:col-span-7 space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#F2A93B] text-[#1E2723] font-bold text-xs">
                    <i data-lucide="zap" class="w-3.5 h-3.5 fill-current"></i>
                    <span>BELANJA HYPERLOCAL MALANG RAYA</span>
                </div>
                <h1 class="font-display font-black text-3xl sm:text-4xl lg:text-5xl leading-tight">
                    Dukung Warung & Kuliner <span class="text-[#F2A93B]">Tetangga Dekatmu.</span>
                </h1>
                <p class="text-emerald-100/90 text-sm sm:text-base max-w-lg">
                    Nikmati sajian khas rumahan, pecel pincuk, rawon gurih, kopi lokal, hingga sembako cepat sampai tanpa ribet.
                </p>
                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <a href="{{ route('explore') }}" class="bg-[#0E9F6E] hover:bg-[#086644] text-white font-bold px-6 py-3 rounded-2xl shadow-md transition flex items-center gap-2 text-sm">
                        <i data-lucide="compass" class="w-4 h-4"></i>
                        <span>Jelajah Toko & Kuliner</span>
                    </a>
                    <a href="{{ route('seller.register') }}" class="bg-white/10 hover:bg-white/20 text-white font-semibold px-5 py-3 rounded-2xl border border-white/20 transition text-sm">
                        Buka Warung / Toko
                    </a>
                </div>
            </div>
            <div class="lg:col-span-5 relative h-64 lg:h-full min-h-[260px] overflow-hidden">
                <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80" alt="Kuliner UMKM" class="w-full h-full object-cover object-center opacity-90 lg:rounded-l-3xl">
                <div class="absolute inset-0 bg-gradient-to-t lg:bg-gradient-to-r from-[#0B5A45] via-transparent to-transparent"></div>
            </div>
        </div>
    </section>

    <!-- 2. Quick Category Tiles (Gojek-like Icons) -->
    <section class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="font-display font-bold text-lg sm:text-xl text-[#0B5A45]">Kategori Pilihan</h2>
            <a href="{{ route('explore') }}" class="text-xs font-bold text-[#0E9F6E] hover:underline flex items-center gap-1">
                <span>Lihat Semua</span>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </a>
        </div>
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-3 sm:gap-4">
            @foreach($categories as $cat)
                <a href="{{ route('explore', ['category' => $cat->id]) }}" class="group bg-white p-3.5 sm:p-4 rounded-2xl border border-gray-100 hover:border-[#0E9F6E] hover:shadow-md transition text-center flex flex-col items-center justify-center gap-2">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-[#EDFDF5] text-[#0E9F6E] group-hover:bg-[#0E9F6E] group-hover:text-white transition flex items-center justify-center shadow-xs">
                        @if($cat->slug === 'kuliner-makanan')
                            <i data-lucide="utensils" class="w-6 h-6"></i>
                        @elseif($cat->slug === 'minuman-kopi')
                            <i data-lucide="coffee" class="w-6 h-6"></i>
                        @elseif($cat->slug === 'sembako-kelontong')
                            <i data-lucide="shopping-basket" class="w-6 h-6"></i>
                        @elseif($cat->slug === 'kue-camilan')
                            <i data-lucide="cookie" class="w-6 h-6"></i>
                        @elseif($cat->slug === 'sayur-buah')
                            <i data-lucide="apple" class="w-6 h-6"></i>
                        @else
                            <i data-lucide="sparkles" class="w-6 h-6"></i>
                        @endif
                    </div>
                    <span class="text-xs font-semibold text-[#1E2723] group-hover:text-[#0E9F6E] transition line-clamp-2 leading-tight">
                        {{ $cat->name }}
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    <!-- 3. Interactive Hyperlocal Map Section -->
    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display font-bold text-lg sm:text-xl text-[#0B5A45] flex items-center gap-2">
                    <i data-lucide="map" class="w-5 h-5 text-[#0E9F6E]"></i>
                    <span>Peta Lokasi Toko Sekitar Anda</span>
                </h2>
                <p class="text-xs text-gray-500">Estimasi jarak akurat dari posisi Anda ke warung/toko mitra.</p>
            </div>
            <span class="text-xs font-semibold bg-emerald-50 text-[#0E9F6E] px-3 py-1 rounded-full border border-emerald-200">
                Hyperlocal Map Active
            </span>
        </div>

        <div class="bg-white p-3 rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div id="hyperlocal-map" class="h-64 sm:h-80 w-full rounded-2xl z-0"></div>
        </div>
    </section>

    <!-- 3.5. Flash Sale / Promo Kilat UMKM (Live Countdown Timer) -->
    @if($discountProducts->isNotEmpty())
        <section class="bg-gradient-to-r from-[#0B5A45] to-[#086644] p-5 sm:p-7 rounded-3xl text-white space-y-4 shadow-lg relative overflow-hidden" x-data="{
            expiry: new Date().setHours(23, 59, 59, 999),
            hours: '00',
            minutes: '00',
            seconds: '00',
            updateTimer() {
                const now = new Date().getTime();
                const diff = this.expiry - now;
                if (diff <= 0) {
                    this.hours = '00';
                    this.minutes = '00';
                    this.seconds = '00';
                    return;
                }
                this.hours = String(Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
                this.minutes = String(Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
                this.seconds = String(Math.floor((diff % (1000 * 60)) / 1000)).padStart(2, '0');
            }
        }" x-init="updateTimer(); setInterval(() => updateTimer(), 1000)">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 relative z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#F2A93B] text-[#1E2723] flex items-center justify-center font-black">
                        <i data-lucide="zap" class="w-5 h-5 fill-current"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-display font-black text-xl text-white">Promo Kilat UMKM 🔥</h2>
                            <span class="bg-[#F2A93B] text-[#1E2723] text-[10px] font-black px-2 py-0.5 rounded-full uppercase">Spesial Hari Ini</span>
                        </div>
                        <p class="text-xs text-emerald-100/80">Penawaran diskon spesial langsung dari mitra UMKM lokal sekitar Anda.</p>
                    </div>
                </div>

                <!-- Live Timer Countdown -->
                <div class="flex items-center gap-1.5 self-start sm:self-auto bg-black/30 backdrop-blur px-3.5 py-1.5 rounded-2xl border border-white/10">
                    <span class="text-xs text-emerald-200 font-semibold mr-1">Berakhir Dalam:</span>
                    <span class="font-mono font-black text-sm bg-white text-[#0B5A45] px-2 py-0.5 rounded-lg" x-text="hours">00</span>
                    <span class="font-bold text-white">:</span>
                    <span class="font-mono font-black text-sm bg-white text-[#0B5A45] px-2 py-0.5 rounded-lg" x-text="minutes">00</span>
                    <span class="font-bold text-white">:</span>
                    <span class="font-mono font-black text-sm bg-[#F2A93B] text-[#1E2723] px-2 py-0.5 rounded-lg" x-text="seconds">00</span>
                </div>
            </div>

            <!-- Discount Products Carousel / Horizontal Scroll -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 pt-2 relative z-10">
                @foreach($discountProducts as $discProd)
                    @php
                        $discPercentage = round((($discProd->compare_at_price - $discProd->price) / $discProd->compare_at_price) * 100);
                    @endphp
                    <div class="bg-white rounded-2xl p-2.5 text-[#1E2723] flex flex-col justify-between hover:shadow-md transition group">
                        <a href="{{ route('products.show', $discProd->slug) }}" class="relative aspect-square w-full rounded-xl overflow-hidden bg-gray-50 block mb-2">
                            <img src="{{ $discProd->image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=300&q=80' }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                            <span class="absolute top-1.5 left-1.5 bg-red-600 text-white text-[10px] font-black px-1.5 py-0.5 rounded shadow-sm">
                                -{{ $discPercentage }}%
                            </span>
                        </a>

                        <div class="space-y-1">
                            <a href="{{ route('products.show', $discProd->slug) }}" class="font-display font-bold text-xs line-clamp-1 group-hover:text-[#0E9F6E] transition">
                                {{ $discProd->name }}
                            </a>
                            <div class="flex flex-col">
                                <span class="font-mono font-black text-sm text-[#0B5A45]">
                                    Rp {{ number_format($discProd->price, 0, ',', '.') }}
                                </span>
                                <span class="text-[10px] text-gray-400 line-through font-mono">
                                    Rp {{ number_format($discProd->compare_at_price, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <form action="{{ route('cart.add') }}" method="POST" class="mt-2">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $discProd->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="w-full bg-[#EDFDF5] hover:bg-[#0E9F6E] text-[#0E9F6E] hover:text-white text-[11px] font-bold py-1.5 rounded-xl transition flex items-center justify-center gap-1">
                                <i data-lucide="plus" class="w-3 h-3"></i>
                                <span>Beli</span>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- 4. Featured Stores with Dynamic Calculated Distance -->
    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display font-bold text-lg sm:text-xl text-[#0B5A45]">Toko & Warung Terdekat</h2>
                <p class="text-xs text-gray-500">Mitra UMKM resmi terverifikasi di area sekitarmu</p>
            </div>
            <a href="{{ route('explore') }}" class="text-xs font-bold text-[#0E9F6E] hover:underline flex items-center gap-1">
                <span>Semua Toko</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($featuredStores as $store)
                <a href="{{ route('stores.show', $store->slug) }}" class="bg-white rounded-2xl border border-gray-100 p-4 hover:border-[#0E9F6E] hover:shadow-lg transition group block">
                    <div class="flex items-start gap-3.5">
                        <img src="{{ $store->logo ?: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=150&q=80' }}" alt="{{ $store->name }}" class="w-16 h-16 rounded-xl object-cover border border-gray-100 shrink-0 group-hover:scale-105 transition">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="bg-[#EDFDF5] text-[#0E9F6E] text-[10px] font-bold px-2 py-0.5 rounded-md">TERVERIFIKASI</span>
                                <span class="text-xs text-gray-400">• {{ $store->operational_hours }}</span>
                            </div>
                            <h3 class="font-display font-bold text-base text-[#1E2723] group-hover:text-[#0E9F6E] truncate transition">
                                {{ $store->name }}
                            </h3>
                            <p class="text-xs text-gray-500 truncate mt-0.5">{{ $store->address }}</p>
                            <div class="flex items-center gap-3 mt-2 text-xs">
                                <div class="flex items-center gap-1 text-[#F2A93B] font-bold">
                                    <i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>
                                    <span>{{ number_format($store->rating, 1) }}</span>
                                    <span class="text-gray-400 font-normal">({{ $store->total_reviews }})</span>
                                </div>
                                <span class="text-gray-300">|</span>
                                <span class="text-[#0E9F6E] font-bold flex items-center gap-1">
                                    <i data-lucide="navigation" class="w-3 h-3 text-[#0E9F6E]"></i>
                                    <span x-text="$store?.userLocation ? $store.userLocation.getDistanceTo({{ $store->latitude }}, {{ $store->longitude }}) : 'Menghitung...'">Menghitung...</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <!-- 5. Terlaris / Popular Products Grid -->
    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display font-bold text-lg sm:text-xl text-[#0B5A45]">Menu & Produk Terlaris 🔥</h2>
                <p class="text-xs text-gray-500">Paling banyak dipesan warga sekitar minggu ini</p>
            </div>
            <a href="{{ route('explore') }}" class="text-xs font-bold text-[#0E9F6E] hover:underline">Lihat Lainnya</a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($popularProducts as $product)
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:border-[#0E9F6E] hover:shadow-md transition flex flex-col group relative">
                    <a href="{{ route('products.show', $product->slug) }}" class="relative aspect-square w-full bg-gray-50 overflow-hidden block">
                        <img src="{{ $product->image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80' }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @if($product->compare_at_price)
                            <span class="absolute top-2 left-2 bg-[#F2A93B] text-[#1E2723] text-[10px] font-black px-2 py-0.5 rounded-md shadow-xs">
                                PROMO
                            </span>
                        @endif
                        @if($product->stock <= 0)
                            <span class="absolute inset-0 bg-black/60 text-white text-xs font-bold flex items-center justify-center">STOK HABIS</span>
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

                        <!-- Direct add to cart button -->
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
            @endforeach
        </div>
    </section>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof L === 'undefined') return;
        const mapElement = document.getElementById('hyperlocal-map');
        if (!mapElement) return;

        let defaultLat = -7.946714;
        let defaultLng = 112.615668;
        if (window.Alpine && window.Alpine.store('userLocation')) {
            defaultLat = window.Alpine.store('userLocation').lat;
            defaultLng = window.Alpine.store('userLocation').lng;
        }

        const map = L.map('hyperlocal-map').setView([defaultLat, defaultLng], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const userIcon = L.divIcon({
            className: 'custom-user-pin',
            html: "<div class='w-6 h-6 rounded-full bg-blue-600 border-2 border-white shadow-lg flex items-center justify-center text-white'><div class='w-2 h-2 rounded-full bg-white animate-ping'></div></div>",
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });

        L.marker([defaultLat, defaultLng], { icon: userIcon })
            .addTo(map)
            .bindPopup('<b>Lokasi Anda Saat Ini</b><br>Menghitung jarak ke warung/toko terdekat.')
            .openPopup();

        const storesData = @json($featuredStores);
        storesData.forEach(st => {
            const dist = window.calculateDistance(defaultLat, defaultLng, st.latitude, st.longitude);
            const storeIcon = L.divIcon({
                className: 'custom-store-pin',
                html: "<div class='w-8 h-8 rounded-2xl bg-[#0E9F6E] border-2 border-white shadow-md flex items-center justify-center text-white text-xs font-bold'>🏪</div>",
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });

            L.marker([st.latitude, st.longitude], { icon: storeIcon })
                .addTo(map)
                .bindPopup(
                    "<div class='p-1'>" +
                        "<b class='text-sm text-[#0B5A45]'>" + st.name + "</b>" +
                        "<p class='text-xs text-gray-600 mt-0.5'>" + st.address + "</p>" +
                        "<div class='mt-1 text-xs font-bold text-[#0E9F6E]'>Jarak: " + dist + "</div>" +
                        "<a href='/toko/" + st.slug + "' class='inline-block mt-2 bg-[#0E9F6E] text-white text-[11px] font-bold px-3 py-1 rounded-lg'>Buka Toko</a>" +
                    "</div>"
                );
        });
    });
</script>
@endsection
