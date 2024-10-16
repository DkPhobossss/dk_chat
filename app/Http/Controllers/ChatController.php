<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Chat_User;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $chatUsers = $chat->users()->select('id', 'name')->get();

        if (!$chatUsers->contains('id', Auth::id())) {
            //403 is more correct, but 404 is better for security reasons
            abort(404);
        }

        $chatMessages = $chat->messages()->get();
        $chatMessages->each(function ($message) use ($chatUsers) {
            $message->user = $chatUsers->firstWhere('id', $message->user_id);
        });

        $lastMessage = $chatMessages->last();
        if ($lastMessage) {
            $chatUser = new Chat_User();
            $chatUser->updateUserLastSeenMessage(Auth::id(), $chat->id, $lastMessage->id);
        }

        $userChats = $this->getAuthUserChats();

        return Inertia::render('Chat/Chat', [
            'chat' => $chat,
            'chats' => $userChats,
            'messages' => $chatMessages,
            'chatUsers' => $chatUsers
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
        ]);

        $currentUserId = Auth::id();

        $searchUser = User::select('id', 'name')
            ->where('id', $validated['user_id'])
            ->where('id', '!=', $currentUserId)
            ->firstOrFail();

        $chatId = $this->getUsersChatId([$currentUserId, $searchUser->id]);


        if ($chatId === null) {
            try {
                DB::beginTransaction();
                $chat = Chat::create([
                    'name' => (strcmp(Auth::user()->name, $searchUser['name']) >= 0
                        ? (Auth::user()->name . ' and ' . $searchUser['name'])
                        : ($searchUser['name'] . ' and ' . Auth::user()->name))
                ]);

                $chat->users()->attach([$currentUserId, $searchUser->id]);

                $chatId = $chat->id;
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return [
                    'status' => 500,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return to_route('chats.show', ['chat' => $chatId]);
    }

    public function getUsersChatId(array $userIds): int|null
    {
        return Chat_User::whereIn('user_id', $userIds)
            ->groupBy('chat_id')
            ->havingRaw('COUNT(chat_id) = ?', [count($userIds)])
            ->value('chat_id');
    }

    private function getAuthUserChats()
    {
        return Auth::user()->chats()->orderByDesc('updated_at')->with(['messages' => function ($query) {
            $query->select('id', 'chat_id', 'body')->latest()->take(1);
        }])->get();
    }
}
