<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function search(Request $request, string $name)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:32',
        ]);

        die($name);
    }
}
