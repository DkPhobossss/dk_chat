<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



Route::prefix('/')->middleware('guest')->group(function () {
    
    Route::get('', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('', [AuthenticatedSessionController::class, 'store']);

});


Route::middleware('auth')->group(function () {
    Route::get('/index', function () {
        return Inertia::render('Home');
    })->name('home');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('test', function() {
        dd(11);
    });
});



require __DIR__.'/auth.php';
