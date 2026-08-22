<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'TokoKita. — Marketplace Hyperlocal UMKM')</title>
    <meta name="description" content="Platform belanja produk & kuliner UMKM lokal terdekat dengan pengiriman cepat dan transaksi aman.">

    <!-- Fonts: Plus Jakarta Sans, Inter, JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Leaflet Map CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#FAF8F2] text-[#1E2723] min-h-screen flex flex-col antialiased selection:bg-[#0E9F6E] selection:text-white pb-20 md:pb-0" x-data="{ localModalOpen: false }">

    <!-- Production Main Navigation -->
    <nav class="bg-white/95 backdrop-blur-md border-b border-[#1E2723]/10 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo & Brand (Text-only "TokoKita.") -->
                <div class="flex items-center gap-6">
                    <a href="{{ route('home') }}" class="group py-1">
                        <span class="font-display font-black text-2xl sm:text-3xl tracking-tight text-[#0B5A45] group-hover:opacity-90 transition">
                            Toko<span class="text-[#0E9F6E]">Kita</span><span class="text-[#F2A93B]">.</span>
                        </span>
                    </a>

                    <!-- Interactive "Kirim ke" Location Selector Button -->
                    <button type="button" @click="localModalOpen = true" class="hidden sm:flex items-center gap-2 bg-[#FAF8F2] hover:bg-[#F2A93B]/15 px-3.5 py-1.5 rounded-full border border-gray-200 text-xs transition cursor-pointer text-left" title="Klik untuk mengubah titik lokasi pengantaran">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#0E9F6E] animate-pulse"></i>
                        <span class="text-gray-500">Kirim ke:</span>
                        <span class="font-semibold text-[#1E2723] max-w-[160px] truncate" x-text="$store?.userLocation?.label ?? '{{ Auth::check() && Auth::user()->defaultAddress ? Auth::user()->defaultAddress->address_line : 'Lowokwaru, Kota Malang' }}'">
                            {{ Auth::check() && Auth::user()->defaultAddress ? Auth::user()->defaultAddress->address_line : 'Lowokwaru, Kota Malang' }}
                        </span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400"></i>
                    </button>
                </div>

                <!-- Search Input Desktop -->
                <div class="hidden md:flex flex-1 max-w-lg mx-6">
                    <form action="{{ route('explore') }}" method="GET" class="w-full relative">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari rawon, pecel, kopi dampit, sembako..." class="w-full bg-[#FAF8F2] border border-gray-200 rounded-full py-2 pl-10 pr-4 text-sm focus:outline-none focus:border-[#0E9F6E] focus:ring-2 focus:ring-[#0E9F6E]/20 transition">
                        <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3.5 top-3"></i>
                    </form>
                </div>

                <!-- User Quick Nav Actions -->
                <div class="flex items-center gap-3">
                    @auth
                        <!-- Chat Action -->
                        <a href="{{ route('chats.index') }}" class="p-2 text-gray-600 hover:text-[#0E9F6E] hover:bg-[#FAF8F2] rounded-full relative transition" title="Pesan Chat">
                            <i data-lucide="message-square" class="w-5 h-5"></i>
                        </a>

                        <!-- Cart Action -->
                        <a href="{{ route('cart') }}" class="p-2 text-gray-600 hover:text-[#0E9F6E] hover:bg-[#FAF8F2] rounded-full relative transition" title="Keranjang Belanja">
                            <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                            @php
                                $cartCount = \App\Models\CartItem::whereHas('cart', fn($q) => $q->where('user_id', Auth::id()))->sum('quantity');
                            @endphp
                            @if($cartCount > 0)
                                <span class="absolute -top-1 -right-1 bg-[#F2A93B] text-[#1E2723] text-[11px] font-bold rounded-full w-5 h-5 flex items-center justify-center shadow-sm">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        </a>

                        <!-- User Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-full border border-gray-200 hover:border-[#0E9F6E] transition">
                                <div class="w-7 h-7 rounded-full bg-[#0E9F6E] text-white font-bold flex items-center justify-center text-xs">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <span class="text-xs font-semibold max-w-[100px] truncate hidden sm:inline">{{ Auth::user()->name }}</span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400"></i>
                            </button>

                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-60 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 animate-in fade-in slide-in-from-top-2 duration-150">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-xs text-gray-500">Masuk sebagai</p>
                                    <p class="text-sm font-bold text-[#0B5A45] truncate">{{ Auth::user()->name }}</p>
                                    <div class="flex items-center gap-1 mt-1 text-[11px] text-[#F2A93B] font-semibold">
                                        <i data-lucide="award" class="w-3.5 h-3.5"></i>
                                        <span>{{ number_format(Auth::user()->loyalty_points) }} Poin</span>
                                    </div>
                                </div>

                                @if(Auth::user()->hasRole('admin'))
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-[#0E9F6E]">
                                        <i data-lucide="shield-alert" class="w-4 h-4 text-emerald-600"></i>
                                        <span>Admin Operations</span>
                                    </a>
                                @endif

                                @if(Auth::user()->hasRole('seller') || Auth::user()->store)
                                    <a href="{{ route('seller.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-[#0E9F6E]">
                                        <i data-lucide="store" class="w-4 h-4 text-emerald-600"></i>
                                        <span>Dashboard Toko Saya</span>
                                    </a>
                                @else
                                    <a href="{{ route('seller.register') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-amber-700 hover:bg-amber-50">
                                        <i data-lucide="plus-circle" class="w-4 h-4 text-amber-600"></i>
                                        <span>Daftar Jadi Mitra Toko</span>
                                    </a>
                                @endif

                                <a href="{{ route('orders.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-[#0E9F6E]">
                                    <i data-lucide="receipt" class="w-4 h-4 text-gray-400"></i>
                                    <span>Pesanan Saya</span>
                                </a>

                                <a href="{{ route('wishlist') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-[#0E9F6E]">
                                    <i data-lucide="heart" class="w-4 h-4 text-gray-400"></i>
                                    <span>Wishlist</span>
                                </a>

                                <a href="{{ route('profile') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-[#0E9F6E]">
                                    <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                                    <span>Profil & Alamat</span>
                                </a>

                                <div class="border-t border-gray-100 my-1"></div>

                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <i data-lucide="log-out" class="w-4 h-4"></i>
                                        <span>Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('seller.register') }}" class="hidden sm:inline-block text-xs font-semibold text-[#0E9F6E] hover:underline px-3 py-1.5">
                            Buka Toko Gratis
                        </a>
                        <a href="{{ route('login') }}" class="text-xs font-semibold bg-[#FAF8F2] text-[#0B5A45] hover:bg-gray-200 px-4 py-2 rounded-xl transition">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="text-xs font-semibold bg-[#0E9F6E] text-white hover:bg-[#086644] px-4 py-2 rounded-xl shadow-sm transition">
                            Daftar
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Global "Kirim Ke" Location Modal Picker -->
    <div x-show="localModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="localModalOpen = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-5 h-5 text-[#0E9F6E]"></i>
                    <h3 class="font-display font-bold text-base text-[#0B5A45]">Pilih Lokasi Pengantaran</h3>
                </div>
                <button type="button" @click="localModalOpen = false" class="p-1.5 rounded-full hover:bg-gray-100 text-gray-400">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Predefined Area Presets -->
            <div class="space-y-2 text-xs">
                <span class="font-bold text-gray-500 block uppercase text-[10px]">Pilih Area / Titik Sekitar Anda:</span>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="$store.userLocation.setLocation(-7.946714, 112.615668, 'Lowokwaru / Soekarno-Hatta'); localModalOpen = false" class="p-3 rounded-2xl border border-gray-200 hover:border-[#0E9F6E] hover:bg-[#EDFDF5] text-left transition font-semibold text-gray-800">
                        📍 Lowokwaru (Suhat)
                    </button>
                    <button type="button" @click="$store.userLocation.setLocation(-7.972341, 112.623412, 'Klojen / Ijen Boulevard'); localModalOpen = false" class="p-3 rounded-2xl border border-gray-200 hover:border-[#0E9F6E] hover:bg-[#EDFDF5] text-left transition font-semibold text-gray-800">
                        📍 Klojen (Ijen)
                    </button>
                    <button type="button" @click="$store.userLocation.setLocation(-7.978210, 112.658219, 'Sawojajar / Danau Toba'); localModalOpen = false" class="p-3 rounded-2xl border border-gray-200 hover:border-[#0E9F6E] hover:bg-[#EDFDF5] text-left transition font-semibold text-gray-800">
                        📍 Sawojajar (Kedungkandang)
                    </button>
                    <button type="button" @click="$store.userLocation.setLocation(-7.871234, 112.527890, 'Kota Wisata Batu'); localModalOpen = false" class="p-3 rounded-2xl border border-gray-200 hover:border-[#0E9F6E] hover:bg-[#EDFDF5] text-left transition font-semibold text-gray-800">
                        📍 Alun-alun Kota Batu
                    </button>
                </div>
            </div>

            @auth
                @if(Auth::user()->addresses->isNotEmpty())
                    <div class="space-y-2 pt-2 border-t border-gray-100 text-xs">
                        <span class="font-bold text-gray-500 block uppercase text-[10px]">Dari Alamat Tersimpan:</span>
                        @foreach(Auth::user()->addresses as $userAddr)
                            <button type="button" @click="$store.userLocation.setLocation({{ $userAddr->latitude }}, {{ $userAddr->longitude }}, '{{ $userAddr->label }} - {{ $userAddr->address_line }}'); localModalOpen = false" class="w-full p-3 rounded-2xl border border-gray-200 hover:border-[#0E9F6E] hover:bg-[#EDFDF5] text-left transition flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-gray-800">{{ $userAddr->label }}</span>
                                    <p class="text-[11px] text-gray-500 truncate max-w-xs">{{ $userAddr->address_line }}</p>
                                </div>
                                <span class="text-[10px] text-[#0E9F6E] font-bold">Gunakan</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            @endauth

            <div class="pt-2 text-center">
                <a href="{{ route('profile') }}" class="text-xs text-[#0E9F6E] font-bold hover:underline">
                    + Pasang Pin Titik Baru di Peta Profil &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Global Flash Alerts -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-3 w-full">
        @if(session('success'))
            <div class="bg-[#EDFDF5] border border-[#0E9F6E]/30 text-[#0B5A45] px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm mb-4">
                <i data-lucide="check-circle" class="w-5 h-5 text-[#0E9F6E] shrink-0"></i>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('info'))
            <div class="bg-[#FEF9EE] border border-[#F2A93B]/40 text-amber-900 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm mb-4">
                <i data-lucide="info" class="w-5 h-5 text-[#F2A93B]"></i>
                <span class="text-sm font-medium">{{ session('info') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm mb-4">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif
    </div>

    <!-- Main Content Slot -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 w-full">
        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation (5 Action Items with Cart Badge) -->
    <aside class="md:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-gray-200 z-50 px-2 py-1.5 shadow-lg">
        <div class="grid grid-cols-5 items-center">
            <a href="{{ route('home') }}" class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('home') ? 'text-[#0E9F6E]' : 'text-gray-500 hover:text-gray-800' }}">
                @if(request()->routeIs('home'))
                    <span class="w-5 h-1 bg-[#0E9F6E] rounded-full mb-1"></span>
                @endif
                <i data-lucide="home" class="w-5 h-5"></i>
                <span class="text-[10px] font-semibold mt-0.5">Beranda</span>
            </a>

            <a href="{{ route('explore') }}" class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('explore') ? 'text-[#0E9F6E]' : 'text-gray-500 hover:text-gray-800' }}">
                @if(request()->routeIs('explore'))
                    <span class="w-5 h-1 bg-[#0E9F6E] rounded-full mb-1"></span>
                @endif
                <i data-lucide="compass" class="w-5 h-5"></i>
                <span class="text-[10px] font-semibold mt-0.5">Jelajah</span>
            </a>

            <a href="{{ route('cart') }}" class="flex flex-col items-center justify-center py-1 relative {{ request()->routeIs('cart') ? 'text-[#0E9F6E]' : 'text-gray-500 hover:text-gray-800' }}">
                @if(request()->routeIs('cart'))
                    <span class="w-5 h-1 bg-[#0E9F6E] rounded-full mb-1"></span>
                @endif
                <div class="relative">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                    @php
                        $mobileCartCount = Auth::check() ? \App\Models\CartItem::whereHas('cart', fn($q) => $q->where('user_id', Auth::id()))->sum('quantity') : 0;
                    @endphp
                    @if($mobileCartCount > 0)
                        <span class="absolute -top-1.5 -right-2 bg-[#F2A93B] text-[#1E2723] text-[9px] font-black rounded-full w-4 h-4 flex items-center justify-center shadow-xs">
                            {{ $mobileCartCount }}
                        </span>
                    @endif
                </div>
                <span class="text-[10px] font-semibold mt-0.5">Keranjang</span>
            </a>

            <a href="{{ route('orders.index') }}" class="flex flex-col items-center justify-center py-1 relative {{ request()->routeIs('orders.*') ? 'text-[#0E9F6E]' : 'text-gray-500 hover:text-gray-800' }}">
                @if(request()->routeIs('orders.*'))
                    <span class="w-5 h-1 bg-[#0E9F6E] rounded-full mb-1"></span>
                @endif
                <i data-lucide="receipt" class="w-5 h-5"></i>
                <span class="text-[10px] font-semibold mt-0.5">Pesanan</span>
            </a>

            <a href="{{ route('profile') }}" class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('profile') ? 'text-[#0E9F6E]' : 'text-gray-500 hover:text-gray-800' }}">
                @if(request()->routeIs('profile'))
                    <span class="w-5 h-1 bg-[#0E9F6E] rounded-full mb-1"></span>
                @endif
                <i data-lucide="user" class="w-5 h-5"></i>
                <span class="text-[10px] font-semibold mt-0.5">Akun</span>
            </a>
        </div>
    </aside>

    <!-- Footer Desktop -->
    <footer class="bg-[#0B5A45] text-white mt-16 pt-12 pb-8 border-t border-emerald-900 hidden md:block">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
            <div class="space-y-3">
                <span class="font-display font-black text-2xl text-white">Toko<span class="text-[#0E9F6E]">Kita</span><span class="text-[#F2A93B]">.</span></span>
                <p class="text-sm text-emerald-100/70">
                    Platform hyperlocal yang menghubungkan UMKM warung, kuliner, dan kriya lokal dengan tetangga di sekitarnya.
                </p>
            </div>
            <div>
                <h4 class="font-display font-bold text-sm text-[#F2A93B] mb-3 uppercase tracking-wider">Navigasi Utama</h4>
                <ul class="space-y-2 text-sm text-emerald-100/80">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition">Beranda</a></li>
                    <li><a href="{{ route('explore') }}" class="hover:text-white transition">Jelajah Kuliner & Toko</a></li>
                    <li><a href="{{ route('seller.register') }}" class="hover:text-white transition">Gabung Jadi Mitra UMKM</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white transition">Masuk Akun</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-display font-bold text-sm text-[#F2A93B] mb-3 uppercase tracking-wider">Layanan Pelanggan</h4>
                <ul class="space-y-2 text-sm text-emerald-100/80">
                    <li><a href="{{ route('orders.index') }}" class="hover:text-white">Lacak Pesanan</a></li>
                    <li><a href="{{ route('chats.index') }}" class="hover:text-white">Pusat Pesan Chat</a></li>
                    <li><a href="{{ route('profile') }}" class="hover:text-white">Pengaturan Akun & Alamat</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-display font-bold text-sm text-[#F2A93B] mb-3 uppercase tracking-wider">Keamanan & Bantuan</h4>
                <p class="text-xs text-emerald-100/70 mb-3">Transaksi aman dengan escrow 1x24 jam anti-fraud & dukungan dispute resolution.</p>
                <div class="flex items-center gap-2 text-xs bg-white/10 px-3 py-2 rounded-xl">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#0E9F6E]"></i>
                    <span>Terverifikasi Toko Kita Guard</span>
                </div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-emerald-800/60 pt-6 text-center text-xs text-emerald-100/50">
            &copy; 2026 TokoKita. Indonesia. Didesain untuk kemajuan UMKM Lokal.
        </div>
    </footer>

    @livewireScripts
    <script>
        lucide.createIcons();
        document.addEventListener('livewire:navigated', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>
