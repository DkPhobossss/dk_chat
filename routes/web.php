<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\RedirectIfAuthenticated as MiddlewareRedirectIfAuthenticated;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(MiddlewareRedirectIfAuthenticated::class)->group(function () {
    Route::get('/', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/', [AuthenticatedSessionController::class, 'store']);

    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/chats', [ChatController::class, 'index'])
        ->name('chats.index');
    Route::get('/chats/{chat}', [ChatController::class, 'show'])
        ->name('chats.show');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

Route::post('/broadcasting/auth', function () {
    return response()->json(['message' => 'Authenticated']);
})->middleware(['auth:sanctum']);
