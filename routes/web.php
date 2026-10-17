<?php

use App\Http\Controllers\Api\PlayerStatsController;
use App\Http\Controllers\Api\VersusController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\RankingController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/ranking', RankingController::class)->name('ranking.index');

    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/estadisticas', PlayerStatsController::class)->name('player-stats');
        Route::get('/vs', VersusController::class)->name('versus');
    });

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::post('/matches', [MatchController::class, 'store'])->name('matches.store');
    Route::put('/matches/{match}', [MatchController::class, 'update'])->name('matches.update');
    Route::delete('/matches/{match}', [MatchController::class, 'destroy'])->name('matches.destroy');

    Route::get('/players', [PlayerController::class, 'index'])->name('players.index');
    Route::post('/players', [PlayerController::class, 'store'])->name('players.store');
    Route::put('/players/{player}', [PlayerController::class, 'update'])->name('players.update');

    Route::get('/imagenes', [ImageController::class, 'index'])->name('imagenes.index');
    Route::post('/imagenes', [ImageController::class, 'store'])->name('imagenes.store');
    Route::delete('/imagenes/{image}', [ImageController::class, 'destroy'])->name('imagenes.destroy');
});
