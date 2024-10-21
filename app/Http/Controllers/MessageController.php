<?php

namespace App\Http\Controllers;

use App\Enums\MessageType;
use App\Events\Chat\MessageEvent;
use App\Http\Resources\MessageResource;
use App\Models\Chat;
use App\Models\Chat_User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function store(Chat $chat, Request $request)
    {
        $validated = $request->validate([
            'body' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $message = Message::create([
                'user_id' => Auth::id(),
                'chat_id' => $chat->id,
                'body' => $validated['body']
            ]);

            $chatUser = new Chat_User();
            $chatUser->updateUserLastSeenMessage(Auth::id(), $chat->id, $message->id);

            $chat->touch();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => 500,
                'message' => $e->getMessage(),
            ];
        }

        broadcast(new MessageEvent($message, MessageType::MessageSent))->toOthers();

        return new MessageResource($message);
    }

    public function update(Chat $chat, Message $message, Request $request)
    {
        $validated = $request->validate([
            'body' => 'required',
        ]);

        $message->body = $validated['body'];
        $message->save();

        broadcast(new MessageEvent($message, MessageType::MessageUpdated))->toOthers();

        return new MessageResource($message);
    }

    public function restore(Chat $chat, Message $message)
    {
        $message->body = $message->deleted_body;
        $message->deleted_body = null;

        $message->save();

        broadcast(new MessageEvent($message, MessageType::MessageRestored))->toOthers();

        return new MessageResource($message);
    }

    public function destroy(Chat $chat, Message $message)
    {
        $message->deleted_body = $message->body;
        $message->body = null;

        $message->save();

        broadcast(new MessageEvent($message, MessageType::MessageDestroyed))->toOthers();

        return new MessageResource($message);
    }

    public function updateLastSeen(Chat $chat, Message $message) 
    {
        return Chat_User::where([
            'chat_id' => $chat->id,
            'user_id' => Auth::id(),
        ])->where('last_seen_message_id', '<', $message->id)
          ->update(['last_seen_message_id' => $message->id]);
    }
}
