<?php

namespace App\Events\Chat;

use App\Enums\MessageType;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message, public MessageType $type) {}

    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->message->chat->id);
    }

    public function getMessageType(): string
    {
        return $this->type->value; 
    }

    public function broadcastWith()
    {
        return (new MessageResource($this->message))->resolve();
    }

    public function broadcastAs(): string
    {
        return $this->type->value;
    }
}
