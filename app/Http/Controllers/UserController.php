<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:32',
        ]);

        //not optimal query! It can be fast with index + name%, but not %name%
        $users = User::select('id', 'name')
            ->where('name', 'like', '%' . $validated['name'] .'%')
            ->where('id' , '!=' , Auth::id())
            ->orderByRaw('LOCATE(?, name) ASC', [$validated['name']]) 
            ->take(5)
            ->get();

        return $users;
    }
}
