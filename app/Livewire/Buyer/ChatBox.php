<?php

namespace App\Livewire\Buyer;

use Livewire\Component;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class ChatBox extends Component
{
    public int $chatId;
    public string $messageText = '';

    public function mount(int $chatId)
    {
        $this->chatId = $chatId;
    }

    public function sendMessage()
    {
        $this->validate([
            'messageText' => 'required|string|max:1000',
        ]);

        Message::create([
            'chat_id' => $this->chatId,
            'sender_id' => Auth::id(),
            'body' => $this->messageText,
        ]);

        $chat = Chat::find($this->chatId);
        if ($chat) {
            $chat->update(['last_message_at' => now()]);
        }

        $this->messageText = '';
    }

    public function render()
    {
        $chat = Chat::with(['store', 'product', 'messages.sender'])->findOrFail($this->chatId);
        return view('livewire.buyer.chat-box', compact('chat'));
    }
}
