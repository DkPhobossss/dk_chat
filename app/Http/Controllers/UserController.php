<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:32',
        ]);

        $users = User::searchByName($validated['name']);
        
        return UserResource::collection($users)->resolve();
    }
}
