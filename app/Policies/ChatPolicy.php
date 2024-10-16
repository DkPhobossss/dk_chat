<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChatPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Chat $chat): Response
    {
        return $chat->users()->find($user->id)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Chat $chat): Response
    {
        return $chat->users()->find($user->id)
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}