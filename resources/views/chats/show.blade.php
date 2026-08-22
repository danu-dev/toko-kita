@extends('layouts.app')

@section('title', 'Obrolan Chat — Toko Kita')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    @livewire('buyer.chat-box', ['chatId' => $chat->id])
</div>
@endsection
