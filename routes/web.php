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
    //in api
    Route::post('/chats', [ChatController::class, 'store'])
        ->name('chats.store');
    Route::get('/chats/{chat}', [ChatController::class, 'show'])
        ->name('chats.show');
    //in api
    Route::get('/search/users', [UserController::class, 'search'])
        ->name('users.search');

    Route::post('/chats/{chat}/messages', [MessageController::class, 'store'])->can('update', 'chat')
        ->name('chats.messages.store');
    Route::put('/chats/{chat}/messages/{message}', [MessageController::class, 'update'])->can('update', 'message')
        ->name('chats.messages.update');
    Route::patch('/chats/{chat}/messages/{message}', [MessageController::class, 'restore'])->can('update', 'message')
        ->name('chats.messages.restore');
    Route::delete('/chats/{chat}/messages/{message}', [MessageController::class, 'destroy'])->can('update', 'message')
        ->name('chats.messages.destroy');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
