<?php

namespace App\Http\Controllers;

use App\Http\Resources\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function store(Chat $chat, Request $request)
    {
        $validated = $request->validate([
            'body' => 'required',
        ]);

        $message = Message::create([
            'user_id' => Auth::id(),
            'chat_id' => $chat->id,
            'body' => $request->body
        ]);

        return new MessageResource($message);
    }

    public function update(Chat $chat, Message $message, Request $request)
    {
        $validated = $request->validate([
            'body' => 'required',
        ]);

        $message->body = $request->body;
        $message->save();

        return new MessageResource($message);
    }

    public function restore(Chat $chat, Message $message)
    {
        $message->body = $message->deleted_body;
        $message->deleted_body = null;

        $message->save();

        return new MessageResource($message);
    }

    public function destroy(Chat $chat, Message $message)
    {
        $message->deleted_body = $message->body;
        $message->body = null;

        $message->save();

        return new MessageResource($message);
    }
}
