@extends('layouts.seller')

@section('title', 'Katalog Produk — ' . $store->name)

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display font-black text-2xl text-[#0B5A45]">Katalog Produk & Menu</h1>
            <p class="text-xs text-gray-500">Kelola daftar menu, varian harga, dan ketersediaan stok tokomu.</p>
        </div>

        <a href="{{ route('seller.products.create') }}" class="bg-[#0E9F6E] hover:bg-[#086644] text-white font-bold px-4 py-2.5 rounded-2xl text-xs shadow-md shadow-[#0E9F6E]/20 flex items-center gap-1.5 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Produk Baru</span>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($products as $prod)
            <div class="bg-white rounded-3xl border border-gray-100 p-4 shadow-sm space-y-3 hover:border-[#0E9F6E] transition flex flex-col justify-between">
                <div class="flex items-start gap-3">
                    <img src="{{ $prod->image ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=200&q=80' }}" class="w-20 h-20 rounded-2xl object-cover border border-gray-100 shrink-0">
                    <div class="min-w-0 flex-1">
                        <span class="text-[10px] bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-semibold">{{ $prod->category->name ?? 'Produk' }}</span>
                        <h3 class="font-bold text-sm text-[#1E2723] truncate mt-1">{{ $prod->name }}</h3>
                        <div class="font-mono font-bold text-sm text-[#0B5A45] mt-0.5">
                            Rp {{ number_format($prod->price, 0, ',', '.') }}
                        </div>
                        <span class="text-xs text-gray-400">Stok: <b>{{ $prod->stock }} {{ $prod->unit }}</b></span>
                    </div>
                </div>

                @if($prod->variants->isNotEmpty())
                    <div class="bg-[#FAF8F2] p-2.5 rounded-xl text-[11px] text-gray-600 space-y-1">
                        <span class="font-bold text-gray-800 block text-[10px] uppercase">Varian:</span>
                        @foreach($prod->variants as $v)
                            <div class="flex justify-between">
                                <span>{{ $v->name }}</span>
                                <span class="font-mono font-semibold">+Rp {{ number_format($v->price_modifier, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="inline-flex items-center gap-1 text-[11px] {{ $prod->is_active ? 'text-[#0E9F6E] font-bold' : 'text-gray-400' }}">
                        <span class="w-2 h-2 rounded-full {{ $prod->is_active ? 'bg-[#0E9F6E]' : 'bg-gray-400' }}"></span>
                        {{ $prod->is_active ? 'Aktif Dijual' : 'Nonaktif' }}
                    </span>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('seller.products.edit', $prod->id) }}" class="p-2 rounded-xl bg-[#FAF8F2] hover:bg-gray-200 text-gray-700 transition">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        </a>
                        <form action="{{ route('seller.products.delete', $prod->id) }}" method="POST" onsubmit="return confirm('Hapus produk ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 transition">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        @empty
            <div class="col-span-full bg-white p-12 text-center rounded-3xl border border-gray-100 text-gray-400">
                Belum ada produk. Tambahkan produk pertama Anda sekarang!
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>

</div>
@endsection
