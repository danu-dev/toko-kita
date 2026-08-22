<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Operations — TokoKita.')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#FAF8F2] text-[#1E2723] min-h-screen flex antialiased">

    <!-- Fixed Left Sidebar for Admin (Deep Teal Background - PRD Section 4.3) -->
    <aside class="w-64 bg-[#0B5A45] text-white flex-col justify-between hidden md:flex shrink-0 min-h-screen sticky top-0">
        <div>
            <!-- Brand -->
            <div class="p-6 border-b border-emerald-900/60">
                <a href="{{ route('admin.dashboard') }}" class="group block">
                    <span class="font-display font-black text-2xl text-white">Toko<span class="text-[#0E9F6E]">Kita</span><span class="text-[#F2A93B]">.</span></span>
                    <span class="block text-[10px] text-red-300 uppercase tracking-wider font-bold mt-0.5">Admin Central</span>
                </a>
            </div>

            <!-- Admin Navigation (Strict Platform Operations) -->
            <nav class="p-4 space-y-1.5 text-xs font-semibold">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-[#0E9F6E] text-white shadow-md' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>Analitik Platform</span>
                </a>

                <a href="{{ route('admin.verifications') }}" class="flex items-center justify-between px-4 py-3 rounded-2xl transition {{ request()->routeIs('admin.verifications*') ? 'bg-[#0E9F6E] text-white shadow-md' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <i data-lucide="user-check" class="w-4 h-4"></i>
                        <span>Verifikasi Mitra</span>
                    </div>
                    @php $pendingCount = \App\Models\Store::where('status', 'pending')->count(); @endphp
                    @if($pendingCount > 0)
                        <span class="bg-[#F2A93B] text-[#1E2723] text-[10px] font-black px-2 py-0.5 rounded-full">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('admin.transactions') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition {{ request()->routeIs('admin.transactions*') ? 'bg-[#0E9F6E] text-white shadow-md' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                    <span>Monitoring Transaksi</span>
                </a>

                <a href="{{ route('admin.categories') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition {{ request()->routeIs('admin.categories*') ? 'bg-[#0E9F6E] text-white shadow-md' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                    <i data-lucide="tags" class="w-4 h-4"></i>
                    <span>Master Kategori</span>
                </a>

                <a href="{{ route('admin.disputes') }}" class="flex items-center justify-between px-4 py-3 rounded-2xl transition {{ request()->routeIs('admin.disputes*') ? 'bg-[#0E9F6E] text-white shadow-md' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                        <span>Pusat Dispute & Komplain</span>
                    </div>
                    @php $openDisputes = \App\Models\Dispute::whereIn('status', ['opened', 'seller_response'])->count(); @endphp
                    @if($openDisputes > 0)
                        <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                            {{ $openDisputes }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('admin.withdrawals') }}" class="flex items-center justify-between px-4 py-3 rounded-2xl transition {{ request()->routeIs('admin.withdrawals*') ? 'bg-[#0E9F6E] text-white shadow-md' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                        <span>Approve Pencairan</span>
                    </div>
                    @php $pendingW = \App\Models\Withdrawal::where('status', 'pending')->count(); @endphp
                    @if($pendingW > 0)
                        <span class="bg-[#F2A93B] text-[#1E2723] text-[10px] font-black px-2 py-0.5 rounded-full">
                            {{ $pendingW }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('admin.settings') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition {{ request()->routeIs('admin.settings*') ? 'bg-[#0E9F6E] text-white shadow-md' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                    <i data-lucide="sliders" class="w-4 h-4"></i>
                    <span>Komisi & Banner</span>
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-emerald-900/60 space-y-2 text-xs">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-emerald-200 hover:text-white py-2 px-3 rounded-xl hover:bg-white/5 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Lihat Frontend App</span>
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 text-red-300 hover:text-red-100 py-2 px-3 rounded-xl hover:bg-white/5 transition">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Keluar Admin</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
        
        <header class="bg-white border-b border-gray-100 px-6 py-3.5 flex items-center justify-between sticky top-0 z-30 shadow-xs">
            <div class="flex items-center gap-2">
                <span class="bg-red-500 text-white text-[10px] font-black px-2 py-0.5 rounded-md">SUPERADMIN</span>
                <span class="text-xs font-bold text-gray-700">Pusat Kendali Operasional Platform</span>
            </div>

            <div class="text-xs text-gray-500">
                Logged in as: <b>{{ Auth::user()->name }}</b>
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
        </div>

        <main class="p-6 flex-1">
            @yield('content')
        </main>
    </div>

    @livewireScripts
    <script>
        lucide.createIcons();
        document.addEventListener('livewire:navigated', () => lucide.createIcons());
        document.addEventListener('livewire:initialized', () => lucide.createIcons());
    </script>
</body>
</html>
