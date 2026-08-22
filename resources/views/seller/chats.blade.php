@extends('layouts.seller')

@section('title', 'Pesan Pelanggan — ' . $store->name)

@section('content')
<div class="space-y-4">
    <div>
        <h1 class="font-display font-black text-2xl text-[#0B5A45]">Pesan & Chat Pelanggan</h1>
        <p class="text-xs text-gray-500">Balas pertanyaan calon pembeli secara real-time untuk meningkatkan penjualan.</p>
    </div>

    @livewire('seller.chat-manager')
</div>
@endsection
