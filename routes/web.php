<?php

use App\Http\Controllers\ChampionPredictionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PredictionController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('games/{game}', [GameController::class, 'show'])->name('games.show');
    Route::get('predictions', [PredictionController::class, 'index'])->name('predictions.index');
    Route::put('games/{game}/prediction', [PredictionController::class, 'upsert'])->name('games.prediction.upsert');
    Route::put('champion-prediction', [ChampionPredictionController::class, 'upsert'])->name('champion-prediction.upsert');
    Route::inertia('rules', 'Rules')->name('rules');
});

require __DIR__.'/settings.php';
