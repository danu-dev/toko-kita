<div class="space-y-4" wire:poll.3s>
    <!-- Chat Header -->
    <div class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('chats.index') }}" class="p-2 rounded-full hover:bg-gray-100 transition">
                <i data-lucide="arrow-left" class="w-4 h-4 text-gray-700"></i>
            </a>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-2xl bg-[#0E9F6E] text-white font-bold flex items-center justify-center text-sm">
                    {{ substr($chat->store->name, 0, 1) }}
                </div>
                <div>
                    <h3 class="font-bold text-sm text-[#1E2723]">{{ $chat->store->name }}</h3>
                    <span class="text-[10px] text-[#0E9F6E] font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0E9F6E] animate-ping"></span> Realtime Chat
                    </span>
                </div>
            </div>
        </div>

        @if($chat->product)
            <div class="text-right hidden sm:block">
                <span class="text-[10px] text-gray-400 block">Produk:</span>
                <span class="text-xs font-bold text-[#0B5A45]">{{ $chat->product->name }}</span>
            </div>
        @endif
    </div>

    <!-- Message Bubble Stream -->
    <div class="bg-white p-4 sm:p-6 rounded-3xl border border-gray-100 shadow-sm h-[420px] overflow-y-auto space-y-3 flex flex-col justify-end">
        @forelse($chat->messages as $msg)
            @php $isMe = ($msg->sender_id === Auth::id()); @endphp
            <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                <div class="max-w-[80%] rounded-2xl p-3 text-xs leading-relaxed {{ $isMe ? 'bg-[#0E9F6E] text-white rounded-tr-none shadow-xs' : 'bg-[#FAF8F2] text-[#1E2723] rounded-tl-none border border-gray-200 shadow-xs' }}">
                    <p>{{ $msg->body }}</p>
                </div>
                <span class="text-[9px] text-gray-400 mt-1 font-mono">
                    {{ $msg->created_at->format('H:i') }}
                </span>
            </div>
        @empty
            <div class="text-center text-xs text-gray-400 py-12">Belum ada pesan. Sampaikan pertanyaan Anda di bawah!</div>
        @endforelse
    </div>

    <!-- Message Input Bar -->
    <form wire:submit="sendMessage" class="flex gap-2">
        <input type="text" wire:model="messageText" required placeholder="Ketik pesan di sini..." class="flex-1 px-4 py-3 bg-white border border-gray-200 rounded-2xl text-xs focus:border-[#0E9F6E] focus:outline-none shadow-sm">
        <button type="submit" class="bg-[#0E9F6E] hover:bg-[#086644] text-white px-5 py-3 rounded-2xl font-bold shadow-md shadow-[#0E9F6E]/20 transition flex items-center justify-center">
            <i data-lucide="send" class="w-4 h-4"></i>
        </button>
    </form>
</div>
