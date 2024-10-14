<?php 
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            return to_route('chats.index'); 
        }

        return $next($request);
    }
}