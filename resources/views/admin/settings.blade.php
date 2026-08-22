@extends('layouts.admin')

@section('title', 'Pengaturan Platform & Banner — Admin Toko Kita')

@section('content')
<div class="max-w-4xl space-y-6">

    <div>
        <h1 class="font-display font-black text-2xl text-[#0B5A45]">Pengaturan Platform & Promosi</h1>
        <p class="text-xs text-gray-500">Konfigurasi persentase potongan komisi transaksi dan kelola banner promosi aplikasi (bisa upload file langsung atau pakai URL).</p>
    </div>

    <!-- Platform Fee Setting Card -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-100 shadow-sm space-y-4">
        <h3 class="font-display font-bold text-base text-[#0B5A45]">Komisi Platform Toko Kita</h3>
        
        <form action="{{ route('admin.settings.update') }}" method="POST" class="flex flex-col sm:flex-row items-end gap-4">
            @csrf
            <div class="flex-1">
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Persentase Potongan Platform (%)</label>
                <div class="relative">
                    <input type="number" step="0.5" name="platform_commission_percent" value="{{ old('platform_commission_percent', $commissionPercent) }}" required class="w-full px-4 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-xl text-sm font-mono font-bold focus:border-[#0E9F6E]">
                    <span class="absolute right-4 top-3 font-bold text-gray-400 text-xs">%</span>
                </div>
            </div>
            <button type="submit" class="bg-[#0E9F6E] hover:bg-[#086644] text-white font-bold px-6 py-2.5 rounded-xl text-xs shadow-md transition">
                Simpan Komisi
            </button>
        </form>
    </div>

    <!-- Banner Promotions Management (Upload File OR URL) -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6" x-data="{ sourceMode: 'upload' }">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-display font-bold text-base text-[#0B5A45]">Banner Promo Homepage</h3>
                <p class="text-xs text-gray-400">Pilih upload file foto dari perangkat Anda atau masukkan URL gambar web.</p>
            </div>
        </div>

        <!-- Add Banner Form with Dual Input (File Upload OR URL) -->
        <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="p-5 bg-[#FAF8F2] rounded-3xl border border-gray-200/80 space-y-4 text-xs">
            @csrf
            
            <div class="flex items-center justify-between">
                <h4 class="font-bold text-[#0B5A45] uppercase text-[11px] flex items-center gap-1.5">
                    <i data-lucide="image-plus" class="w-4 h-4"></i>
                    <span>Tambah Banner Baru</span>
                </h4>

                <!-- Source Switcher Tabs -->
                <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-gray-200">
                    <button type="button" @click="sourceMode = 'upload'" class="px-3 py-1 rounded-lg font-bold transition" :class="sourceMode === 'upload' ? 'bg-[#0E9F6E] text-white shadow-xs' : 'text-gray-600 hover:text-gray-900'">
                        Upload File
                    </button>
                    <button type="button" @click="sourceMode = 'url'" class="px-3 py-1 rounded-lg font-bold transition" :class="sourceMode === 'url' ? 'bg-[#0E9F6E] text-white shadow-xs' : 'text-gray-600 hover:text-gray-900'">
                        Pakai URL
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-gray-700 font-bold mb-1">Judul Promosi</label>
                    <input type="text" name="title" required placeholder="Contoh: Diskon Kuliner Malam Malang" class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-1">Badge Teks (Tag Promo)</label>
                    <input type="text" name="badge_text" placeholder="SUPER HEMAT / DISKON 50%" class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl">
                </div>
                
                <div class="sm:col-span-2">
                    <label class="block text-gray-700 font-bold mb-1">Tautan Tujuan (Link Ketika Diklik)</label>
                    <input type="text" name="link" placeholder="/jelajah atau https://..." value="/jelajah" class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl">
                </div>

                <!-- Mode 1: File Upload -->
                <div class="sm:col-span-2" x-show="sourceMode === 'upload'">
                    <label class="block text-gray-700 font-bold mb-1">Pilih File Banner dari Komputer/HP (JPG, PNG, WebP)</label>
                    <input type="file" name="banner_file" accept="image/*" class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-[#0E9F6E] hover:file:bg-emerald-100 cursor-pointer">
                </div>

                <!-- Mode 2: URL Input -->
                <div class="sm:col-span-2" x-show="sourceMode === 'url'" x-cloak>
                    <label class="block text-gray-700 font-bold mb-1">URL Gambar Banner Eksternal</label>
                    <input type="url" name="banner_url" placeholder="https://images.unsplash.com/..." class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl">
                </div>
            </div>

            <button type="submit" class="bg-[#0E9F6E] hover:bg-[#086644] text-white font-bold px-6 py-2.5 rounded-xl text-xs shadow-md shadow-[#0E9F6E]/20 transition flex items-center gap-1.5">
                <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                <span>Terbitkan Banner Sekarang</span>
            </button>
        </form>

        <!-- Existing Banners List -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($banners as $b)
                <div class="relative rounded-2xl overflow-hidden border border-gray-200 shadow-xs group bg-gray-900">
                    <img src="{{ $b->image }}" class="w-full h-40 object-cover opacity-85 group-hover:scale-105 transition duration-300">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent p-4 flex flex-col justify-end text-white">
                        <span class="bg-[#F2A93B] text-[#1E2723] text-[9px] font-black px-2 py-0.5 rounded w-max mb-1 shadow-xs">{{ $b->badge_text }}</span>
                        <h4 class="font-bold text-sm leading-tight">{{ $b->title }}</h4>
                        <span class="text-[10px] text-gray-300 truncate mt-0.5">Link: {{ $b->link }}</span>
                    </div>
                    <form action="{{ route('admin.banners.delete', $b->id) }}" method="POST" class="absolute top-2 right-2 z-10" onsubmit="return confirm('Hapus banner promosi ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-xl bg-black/60 hover:bg-red-600 text-white transition shadow-sm">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

    </div>

    <!-- Voucher & Promo Coupon Management (PRD Section 5.3) -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6" x-data="{ type: 'fixed' }">
        <div>
            <h3 class="font-display font-bold text-base text-[#0B5A45]">Kupon & Voucher Promo Platform</h3>
            <p class="text-xs text-gray-400">Terbitkan kode promo potongan harga nominal (Rp) atau persentase (%) untuk mendorong transaksi pembeli.</p>
        </div>

        <!-- Add Coupon Form -->
        <form action="{{ route('admin.coupons.store') }}" method="POST" class="p-5 bg-[#FAF8F2] rounded-3xl border border-gray-200/80 space-y-4 text-xs">
            @csrf
            
            <h4 class="font-bold text-[#0B5A45] uppercase text-[11px] flex items-center gap-1.5">
                <i data-lucide="ticket" class="w-4 h-4"></i>
                <span>Buat Kupon Promo Baru</span>
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-gray-700 font-bold mb-1">Kode Voucher</label>
                    <input type="text" name="code" required placeholder="MISAL: LEBARAN2026" class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl font-mono uppercase font-bold text-[#0B5A45]">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-1">Judul / Deskripsi Singkat</label>
                    <input type="text" name="title" required placeholder="Diskon Spesial Pengguna Baru" class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-1">Tipe Diskon</label>
                    <select name="type" x-model="type" class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl font-semibold">
                        <option value="fixed">Nominal Tetap (Rp)</option>
                        <option value="percent">Persentase (%)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-gray-700 font-bold mb-1">
                        <span x-text="type === 'fixed' ? 'Besar Potongan (Rp)' : 'Besar Potongan (%)'">Besar Potongan</span>
                    </label>
                    <input type="number" name="discount_value" required min="1" placeholder="10000" class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl font-mono">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-1">Min. Belanja (Rp)</label>
                    <input type="number" name="min_order_amount" required min="0" value="0" placeholder="30000" class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl font-mono">
                </div>
                <div x-show="type === 'percent'">
                    <label class="block text-gray-700 font-bold mb-1">Maks. Diskon (Rp - Opsional)</label>
                    <input type="number" name="max_discount" placeholder="25000" class="w-full px-3.5 py-2 bg-white border border-gray-200 rounded-xl font-mono">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-[#0B5A45] hover:bg-[#084233] text-white font-bold px-6 py-2 rounded-xl text-xs flex items-center gap-1.5 transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Terbitkan Kupon</span>
                </button>
            </div>
        </form>

        <!-- Coupons Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-gray-100 text-gray-400 uppercase text-[10px] text-left">
                        <th class="py-2.5 font-bold">Kode</th>
                        <th class="py-2.5 font-bold">Judul Promo</th>
                        <th class="py-2.5 font-bold">Diskon</th>
                        <th class="py-2.5 font-bold">Min. Belanja</th>
                        <th class="py-2.5 text-center font-bold">Status</th>
                        <th class="py-2.5 text-right font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($coupons as $cp)
                        <tr class="hover:bg-[#FAF8F2]/60 transition">
                            <td class="py-3 font-mono font-bold text-[#0B5A45]">{{ $cp->code }}</td>
                            <td class="py-3 font-semibold text-gray-800">{{ $cp->title }}</td>
                            <td class="py-3 font-mono">
                                @if($cp->type === 'percent')
                                    <span class="font-bold text-[#F2A93B]">{{ (int)$cp->discount_value }}%</span>
                                    @if($cp->max_discount)
                                        <span class="text-gray-400 text-[10px] block">Maks Rp {{ number_format($cp->max_discount, 0, ',', '.') }}</span>
                                    @endif
                                @else
                                    <span class="font-bold text-[#0E9F6E]">Rp {{ number_format($cp->discount_value, 0, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="py-3 font-mono text-gray-600">Rp {{ number_format($cp->min_order_amount, 0, ',', '.') }}</td>
                            <td class="py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $cp->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $cp->is_active ? 'Aktif' : 'Non-aktif' }}
                                </span>
                            </td>
                            <td class="py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <form action="{{ route('admin.coupons.toggle', $cp->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg border border-gray-200 hover:bg-gray-100 text-[10px] font-bold text-gray-700 transition">
                                            {{ $cp->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.coupons.delete', $cp->id) }}" method="POST" onsubmit="return confirm('Hapus kupon ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 transition">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-gray-400">Belum ada kupon voucher yang dibuat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
