<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

Broadcast::channel('user.{id}', function (User $user, $id) {
    return $user->id === (int) $id;
});

Broadcast::channel('chat.{id}', function (User $user, $id) {
    return $user->chats()->where('id', $id)->exists();
});
