<?php

use App\Http\Controllers\Api\PlayerStatsController;
use App\Http\Controllers\Api\VersusController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\PlayerController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/estadisticas', PlayerStatsController::class)->name('player-stats');
        Route::get('/vs', VersusController::class)->name('versus');
    });

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::post('/matches', [MatchController::class, 'store'])->name('matches.store');
    Route::put('/matches/{match}', [MatchController::class, 'update'])->name('matches.update');

    Route::post('/players', [PlayerController::class, 'store'])->name('players.store');
});
