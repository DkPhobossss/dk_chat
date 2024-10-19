<?php

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/chats/list', [ChatController::class, 'list'])
        ->name('chats.list');
    Route::post('/chats', [ChatController::class, 'store'])
        ->name('chats.store');
    Route::get('/chats/{chat}/data', [ChatController::class, 'data'])
        ->name('chats.data'); 
    
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
});
