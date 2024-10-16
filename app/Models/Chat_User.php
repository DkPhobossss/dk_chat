<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat_User extends Model
{
    protected $table = 'chat_user';
    protected $fillable = ['last_seen_message_id'];
    public $timestamps = false;

    use HasFactory;

    public function updateUserLastSeenMessage(int $userId, int $chatId, int $messageId)
    {
        return $this
            ->where('user_id', $userId)
            ->where('chat_id', $chatId)
            ->update(['last_seen_message_id' => $messageId]);
    }
}
