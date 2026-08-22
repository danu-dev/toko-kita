<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class ChatManager extends Component
{
    public ?int $selectedChatId = null;
    public string $messageText = '';
    public string $searchQuery = '';

    public function mount(?int $chatId = null)
    {
        if ($chatId) {
            $this->selectedChatId = $chatId;
        } else {
            $first = Chat::orderByDesc('last_message_at')->first();
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
        $chatsQuery = Chat::with(['buyer', 'store', 'product', 'messages' => fn($q) => $q->latest()->take(1)])
            ->orderByDesc('last_message_at');

        if (!empty($this->searchQuery)) {
            $query = $this->searchQuery;
            $chatsQuery->where(function($q) use ($query) {
                $q->whereHas('buyer', fn($b) => $b->where('name', 'like', "%{$query}%"))
                  ->orWhereHas('store', fn($s) => $s->where('name', 'like', "%{$query}%"));
            });
        }

        $chats = $chatsQuery->get();

        $selectedChat = null;
        if ($this->selectedChatId) {
            $selectedChat = Chat::with(['buyer', 'store', 'product', 'messages.sender'])->find($this->selectedChatId);
        }

        return view('livewire.admin.chat-manager', compact('chats', 'selectedChat'));
    }
}
