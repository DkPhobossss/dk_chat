<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChatResource;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function index()
    {
        $userChats = $this->getAuthUserChats();

        return Inertia::render('Chat/Chats', [
            'chats' => $userChats,
        ]);
    }

    public function show(Chat $chat)
    {
        $chatUsers = $chat->users()->get();

        if ( !$chatUsers->find(Auth::id()) ) {
            //403 is more correct, but 404 is better for security reasons
            abort(404);
        }

        $userChats = $this->getAuthUserChats();
        $chatMessages = $chat->messages()->with('user:id,name')->get();

        return Inertia::render('Chat/Chat', [
            'chat' => $chat,
            'chats'=> $userChats,
            'messages' => $chatMessages,
            'chatUsers' => $chatUsers
        ]);
    }

    private function getAuthUserChats() {
        return Auth::user()->chats()->orderByDesc('pivot_updated_at')->get();
    }
}
