<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Chat is strictly peer-to-peer between Buyer and Seller.
     * Admin does NOT snoop or participate in private store chats.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.disputes')->with('info', 'Admin mengelola penyelesaian kendala pesanan melalui Pusat Dispute & Komplain.');
        }

        if ($user->hasRole('seller') && $user->store) {
            $store = $user->store;
            return view('seller.chats', compact('store'));
        }

        $chats = Chat::with(['store', 'product', 'messages' => fn($q) => $q->latest()->take(1)])
            ->where('buyer_id', $user->id)
            ->orderByDesc('last_message_at')
            ->get();

        return view('chats.index', compact('chats'));
    }

    public function show(int $id)
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            abort(403, 'Admin tidak memiliki akses ke percakapan privat warung/pembeli.');
        }

        if ($user->hasRole('seller') && $user->store) {
            $store = $user->store;
            return view('seller.chats', compact('store'));
        }

        $chat = Chat::with(['buyer', 'store', 'product', 'messages.sender'])->findOrFail($id);

        if (Auth::id() !== $chat->buyer_id && Auth::id() !== $chat->store->user_id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        Message::where('chat_id', $chat->id)
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('chats.show', compact('chat'));
    }

    public function startChat(Request $request)
    {
        $storeId = $request->input('store_id');
        $productId = $request->input('product_id');

        $chat = Chat::firstOrCreate([
            'buyer_id' => Auth::id(),
            'store_id' => $storeId,
            'product_id' => $productId,
        ], [
            'last_message_at' => now(),
        ]);

        return redirect()->route('chats.show', $chat->id);
    }
}
