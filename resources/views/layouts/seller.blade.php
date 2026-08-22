<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Mitra — Toko Kita')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#FAF8F2] text-[#1E2723] min-h-screen flex antialiased">

    <!-- Fixed Left Sidebar (Desktop - PRD Section 4.3 Deep Teal background) -->
    <aside class="w-64 bg-[#0B5A45] text-white flex-col justify-between hidden md:flex shrink-0 min-h-screen sticky top-0">
        <div>
            <!-- Brand -->
            <div class="p-6 border-b border-emerald-900/60">
                <a href="{{ route('seller.dashboard') }}" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-[#0E9F6E] flex items-center justify-center text-white shadow-md">
                        <i data-lucide="store" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="font-display font-black text-lg text-white">toko<span class="text-[#F2A93B]">kita</span></span>
                        <span class="block text-[10px] text-emerald-200 uppercase tracking-wider font-semibold">Mitra Portal</span>
                    </div>
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5 text-xs font-semibold">
                <a href="{{ route('seller.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition {{ request()->routeIs('seller.dashboard') ? 'bg-[#0E9F6E] text-white shadow-md' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>Ringkasan Toko</span>
                </a>

                <a href="{{ route('seller.orders') }}" class="flex items-center justify-between px-4 py-3 rounded-2xl transition {{ request()->routeIs('seller.orders*') ? 'bg-[#0E9F6E] text-white shadow-md' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                        <span>Kelola Pesanan</span>
                    </div>
                    @php
                        $pendingOrdersCount = \App\Models\Order::where('store_id', Auth::user()->store?->id)->where('status', 'menunggu_konfirmasi')->count();
                    @endphp
                    @if($pendingOrdersCount > 0)
                        <span class="bg-[#F2A93B] text-[#1E2723] text-[10px] font-black px-2 py-0.5 rounded-full animate-bounce">
                            {{ $pendingOrdersCount }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('seller.products') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition {{ request()->routeIs('seller.products*') ? 'bg-[#0E9F6E] text-white shadow-md' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                    <i data-lucide="package" class="w-4 h-4"></i>
                    <span>Katalog Produk</span>
                </a>

                <a href="{{ route('seller.wallet') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition {{ request()->routeIs('seller.wallet*') ? 'bg-[#0E9F6E] text-white shadow-md' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                    <span>Saldo & Penarikan</span>
                </a>

                <a href="{{ route('seller.reports') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition {{ request()->routeIs('seller.reports*') ? 'bg-[#0E9F6E] text-white shadow-md' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                    <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                    <span>Laporan & Ulasan</span>
                </a>

                <a href="{{ route('chats.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition {{ request()->routeIs('chats*') ? 'bg-[#0E9F6E] text-white shadow-md' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                    <span>Pesan Pelanggan</span>
                </a>

                <a href="{{ route('seller.settings') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition {{ request()->routeIs('seller.settings*') ? 'bg-[#0E9F6E] text-white shadow-md' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    <span>Pengaturan Toko</span>
                </a>
            </nav>
        </div>

        <!-- Store Switch / Logout footer -->
        <div class="p-4 border-t border-emerald-900/60 space-y-2 text-xs">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-emerald-200 hover:text-white py-2 px-3 rounded-xl hover:bg-white/5 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Toko Kita</span>
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 text-red-300 hover:text-red-100 py-2 px-3 rounded-xl hover:bg-white/5 transition">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Keluar Akun</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
        
        <!-- Seller Top Bar -->
        <header class="bg-white border-b border-gray-100 px-6 py-3.5 flex items-center justify-between sticky top-0 z-30 shadow-xs">
            <div class="flex items-center gap-3">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-[#0E9F6E] animate-ping"></span>
                <div>
                    <h2 class="font-display font-bold text-sm text-[#0B5A45]">{{ Auth::user()->store?->name ?? 'Toko Saya' }}</h2>
                    <span class="text-[11px] text-gray-400">Status Toko: <b class="text-[#0E9F6E] uppercase">{{ Auth::user()->store?->status }}</b></span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('stores.show', Auth::user()->store?->slug ?? '') }}" target="_blank" class="text-xs bg-[#FAF8F2] hover:bg-[#0E9F6E] hover:text-white px-3.5 py-1.5 rounded-xl font-bold border border-gray-200 flex items-center gap-1.5 transition">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    <span>Lihat Toko Publik</span>
                </a>
            </div>
        </header>

        <!-- Flash messages -->
        <div class="p-6 pb-0">
            @if(session('success'))
                <div class="bg-[#EDFDF5] border border-[#0E9F6E]/30 text-[#0B5A45] px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm mb-4">
                    <i data-lucide="check-circle" class="w-5 h-5 text-[#0E9F6E]"></i>
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

        <main class="p-6 flex-1">
            @yield('content')
        </main>
    </div>

    @livewireScripts
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
