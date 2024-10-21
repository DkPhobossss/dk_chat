<?php

namespace App\Events\Chat;

use App\Http\Resources\ChatResource;
use App\Models\Chat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatCreatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Chat $chat) {}

    public function broadcastOn(): array
    {
        return $this->chat->users()->get()->map(function ($user) {
            return new PrivateChannel('user.' . $user->id);
        })->toArray();
    }

    public function broadcastWith()
    {
        return (new ChatResource($this->chat))->resolve();
    }

    public function broadcastAs(): string
    {
        return 'chat_created';
    }
}
