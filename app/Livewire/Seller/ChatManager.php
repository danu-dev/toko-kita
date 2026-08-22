<?php

namespace App\Livewire\Seller;

use Livewire\Component;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class ChatManager extends Component
{
    public ?int $selectedChatId = null;
    public string $messageText = '';

    public function mount(?int $chatId = null)
    {
        $store = Auth::user()->store;
        if (!$store) return;

        if ($chatId) {
            $this->selectedChatId = $chatId;
        } else {
            $first = Chat::where('store_id', $store->id)->orderByDesc('last_message_at')->first();
            if ($first) {
                $this->selectedChatId = $first->id;
            }
        }
    }

    public function selectChat(int $chatId)
    {
        $this->selectedChatId = $chatId;
    }

    public function sendMessage()
    {
        $this->validate([
            'messageText' => 'required|string|max:1000',
        ]);

        if (!$this->selectedChatId) return;

        Message::create([
            'chat_id' => $this->selectedChatId,
            'sender_id' => Auth::id(),
            'body' => $this->messageText,
        ]);

        $chat = Chat::find($this->selectedChatId);
        if ($chat) {
            $chat->update(['last_message_at' => now()]);
        }

        $this->messageText = '';
    }

    public function render()
    {
        $store = Auth::user()->store;
        $chats = collect();
        $selectedChat = null;

        if ($store) {
            $chats = Chat::with(['buyer', 'product', 'messages' => fn($q) => $q->latest()->take(1)])
                ->where('store_id', $store->id)
                ->orderByDesc('last_message_at')
                ->get();

            if ($this->selectedChatId) {
                $selectedChat = Chat::with(['buyer', 'product', 'messages.sender'])
                    ->where('store_id', $store->id)
                    ->find($this->selectedChatId);
            }
        }

        return view('livewire.seller.chat-manager', compact('chats', 'selectedChat'));
    }
}
