@extends('layouts.app')

@section('title', 'Pesan & Chat — Toko Kita')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="font-display font-black text-2xl text-[#0B5A45]">Pesan & Chat</h1>
        <p class="text-xs text-gray-500">Komunikasi langsung antara pembeli dan penjual mitra UMKM.</p>
    </div>

    @if($chats->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-gray-100 shadow-sm space-y-3">
            <i data-lucide="message-square" class="w-12 h-12 text-gray-300 mx-auto"></i>
            <h3 class="font-bold text-gray-700">Belum ada obrolan chat</h3>
            <p class="text-xs text-gray-400">Buka halaman toko atau produk lalu klik "Chat Penjual" untuk memulai.</p>
        </div>
    @else
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm divide-y divide-gray-100 overflow-hidden">
            @foreach($chats as $c)
                @php
                    $otherParty = Auth::user()->hasRole('seller') ? $c->buyer : $c->store;
                    $lastMsg = $c->messages->first();
                @endphp
                <a href="{{ route('chats.show', $c->id) }}" class="p-4 sm:p-5 flex items-center justify-between hover:bg-[#FAF8F2] transition block">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-[#0E9F6E] text-white font-bold flex items-center justify-center text-base">
                            {{ substr($otherParty->name ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-[#1E2723]">{{ $otherParty->name ?? 'Pengguna' }}</h4>
                            <p class="text-xs text-gray-500 line-clamp-1 mt-0.5">
                                {{ $lastMsg ? $lastMsg->body : 'Mulai percakapan...' }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right text-[11px] text-gray-400">
                        {{ $c->last_message_at?->diffForHumans() }}
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
