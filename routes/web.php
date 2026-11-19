<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\NikniqController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GameController::class, 'play'])->name('home');
Route::get('/leaderboard', [GameController::class, 'leaderboard'])->name('leaderboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');

    // Sign in / register with Google or nikniq.com (each 404s until configured in .env).
    Route::middleware('throttle:20,1')->group(function () {
        Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
        Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
        Route::get('/auth/nikniq', [NikniqController::class, 'redirect'])->name('auth.nikniq');
        Route::get('/auth/nikniq/callback', [NikniqController::class, 'callback'])->name('auth.nikniq.callback');
        // The portal's default registered redirect URI for every game.
        Route::get('/auth/callback', [NikniqController::class, 'callback'])->name('auth.callback');
    });
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/games', [GameController::class, 'store'])->name('games.store');
    Route::get('/my-games', [GameController::class, 'history'])->name('history');
});
