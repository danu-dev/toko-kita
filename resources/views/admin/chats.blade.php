@extends('layouts.admin')

@section('title', 'Pusat Chat & Moderasi — Admin Toko Kita')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display font-black text-2xl text-[#0B5A45]">Pusat Chat & Pesan Real-time</h1>
            <p class="text-xs text-gray-500">Supervisi interaksi chat antara mitra penjual dan pembeli secara langsung.</p>
        </div>
    </div>

    @livewire('admin.chat-manager')
</div>
@endsection
