<div class="h-[calc(100vh-180px)] sm:h-[calc(100vh-140px)] flex flex-col md:flex-row bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden" wire:poll.3s>
    
    <!-- Left Chat List (Admin Supervision & Direct Chat) -->
    <div class="w-full md:w-80 border-r border-gray-100 flex flex-col h-full bg-[#FAF8F2]/60">
        <div class="p-4 border-b border-gray-100 space-y-2">
            <h3 class="font-display font-bold text-sm text-[#0B5A45] flex items-center justify-between">
                <span>Pusat Chat & Pesan</span>
                <span class="bg-red-100 text-red-700 text-[10px] px-2 py-0.5 rounded-full font-bold">Admin Hub</span>
            </h3>
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Cari nama toko/pembeli..." class="w-full pl-8 pr-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs focus:border-[#0E9F6E] focus:outline-none">
                <i data-lucide="search" class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2"></i>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto divide-y divide-gray-100">
            @forelse($chats as $c)
                <button type="button" wire:click="selectChat({{ $c->id }})" class="w-full p-3.5 text-left flex items-start gap-3 transition {{ $selectedChatId === $c->id ? 'bg-[#EDFDF5] border-l-4 border-[#0E9F6E]' : 'hover:bg-white/80' }}">
                    <div class="w-10 h-10 rounded-2xl bg-[#0E9F6E] text-white font-bold flex items-center justify-center text-xs shrink-0 shadow-xs">
                        {{ substr($c->store->name ?? 'T', 0, 1) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-xs text-[#1E2723] truncate">{{ $c->store->name }}</h4>
                            <span class="text-[9px] text-gray-400 font-mono">{{ $c->last_message_at?->format('H:i') }}</span>
                        </div>
                        <p class="text-[11px] text-gray-500 truncate mt-0.5">Pembeli: <b>{{ $c->buyer->name }}</b></p>
                        <p class="text-[10px] text-gray-400 truncate mt-0.5">
                            {{ $c->messages->first()?->body ?? 'Percakapan baru...' }}
                        </p>
                    </div>
                </button>
            @empty
                <div class="p-8 text-center text-xs text-gray-400">Belum ada percakapan.</div>
            @endforelse
        </div>
    </div>

    <!-- Right Real-time Conversation View -->
    <div class="flex-1 flex flex-col h-full bg-white">
        @if($selectedChat)
            <!-- Chat Header -->
            <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#0B5A45] text-white font-bold flex items-center justify-center text-sm">
                        {{ substr($selectedChat->store->name, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="font-bold text-xs text-gray-900 flex items-center gap-2">
                            <span>{{ $selectedChat->store->name }}</span>
                            <span class="text-gray-300">↔</span>
                            <span>{{ $selectedChat->buyer->name }}</span>
                        </h4>
                        <p class="text-[10px] text-gray-400">
                            @if($selectedChat->product)
                                Produk Terkait: <b>{{ $selectedChat->product->name }}</b>
                            @else
                                Chat Umum
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 text-xs text-[#0E9F6E] font-semibold">
                    <span class="w-2 h-2 rounded-full bg-[#0E9F6E] animate-ping"></span>
                    <span class="text-[10px]">Realtime Active</span>
                </div>
            </div>

            <!-- Messages Stream -->
            <div class="flex-1 p-4 overflow-y-auto space-y-3 flex flex-col justify-end bg-[#FAF8F2]/30">
                @foreach($selectedChat->messages as $msg)
                    @php $isMe = ($msg->sender_id === Auth::id()); @endphp
                    <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                        <div class="flex items-center gap-1 mb-0.5">
                            <span class="text-[10px] font-bold text-gray-500">{{ $msg->sender->name ?? 'User' }}</span>
                            @if($msg->sender?->hasRole('admin'))
                                <span class="text-[9px] bg-red-100 text-red-700 px-1 rounded font-bold">ADMIN</span>
                            @elseif($msg->sender?->hasRole('seller'))
                                <span class="text-[9px] bg-amber-100 text-amber-800 px-1 rounded font-bold">TOKO</span>
                            @endif
                        </div>
                        <div class="max-w-[75%] rounded-2xl p-3 text-xs leading-relaxed {{ $isMe ? 'bg-[#0E9F6E] text-white rounded-tr-none shadow-xs' : 'bg-white text-gray-800 rounded-tl-none border border-gray-200 shadow-xs' }}">
                            {{ $msg->body }}
                        </div>
                        <span class="text-[9px] text-gray-400 font-mono mt-0.5">{{ $msg->created_at->format('H:i') }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Chat Input Form -->
            <form wire:submit="sendMessage" class="p-3 border-t border-gray-100 bg-white flex gap-2">
                <input type="text" wire:model="messageText" placeholder="Ketik pesan balasan (Admin/Moderator)..." class="flex-1 px-4 py-2.5 bg-[#FAF8F2] border border-gray-200 rounded-2xl text-xs focus:border-[#0E9F6E] focus:outline-none">
                <button type="submit" class="bg-[#0E9F6E] hover:bg-[#086644] text-white px-5 py-2.5 rounded-2xl text-xs font-bold shadow-md shadow-[#0E9F6E]/20 transition flex items-center gap-1.5">
                    <span>Kirim</span>
                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                </button>
            </form>
        @else
            <div class="flex-1 flex flex-col items-center justify-center text-gray-400 p-8 text-center">
                <i data-lucide="message-square" class="w-12 h-12 text-gray-300 mb-2"></i>
                <p class="text-xs">Pilih percakapan di sebelah kiri untuk melihat pesan.</p>
            </div>
        @endif
    </div>

</div>
