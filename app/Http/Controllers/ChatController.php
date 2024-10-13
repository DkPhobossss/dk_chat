<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function index(): Response
    {
        $chats = Auth::user()->chats()->orderByDesc('updated_at');

        dd(Auth::user()->chats());

        return Inertia::render('Chat/Home', [
            'chats' => $chats,
        ]);
    }
}
