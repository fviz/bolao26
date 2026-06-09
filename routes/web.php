<?php

use App\Http\Controllers\ChampionPredictionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameCommentController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\TopScorerPredictionController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('games/{game}', [GameController::class, 'show'])->name('games.show');
    Route::get('predictions', [PredictionController::class, 'index'])->name('predictions.index');
    Route::get('ranking', [RankingController::class, 'index'])->name('ranking.index');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('notifications/broadcast', [NotificationController::class, 'storeBroadcast'])
        ->middleware('admin')
        ->name('notifications.broadcast.store');
    Route::get('push-subscriptions/vapid-key', [PushSubscriptionController::class, 'vapidKey'])->name('push-subscriptions.vapid-key');
    Route::post('push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push-subscriptions.store');
    Route::post('push-subscriptions/test', [PushSubscriptionController::class, 'sendTest'])
        ->middleware('throttle:6,1')
        ->name('push-subscriptions.test');
    Route::delete('push-subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push-subscriptions.destroy');
    Route::put('games/{game}/prediction', [PredictionController::class, 'upsert'])->name('games.prediction.upsert');
    Route::post('games/{game}/comments', [GameCommentController::class, 'store'])->name('games.comments.store');
    Route::delete('games/{game}/comments/{comment}', [GameCommentController::class, 'destroy'])
        ->name('games.comments.destroy')
        ->scopeBindings();
    Route::put('champion-prediction', [ChampionPredictionController::class, 'upsert'])->name('champion-prediction.upsert');
    Route::delete('champion-prediction', [ChampionPredictionController::class, 'destroy'])->name('champion-prediction.destroy');
    Route::put('top-scorer-prediction', [TopScorerPredictionController::class, 'upsert'])->name('top-scorer-prediction.upsert');
    Route::delete('top-scorer-prediction', [TopScorerPredictionController::class, 'destroy'])->name('top-scorer-prediction.destroy');
    Route::inertia('rules', 'Rules')->name('rules');
});

require __DIR__.'/settings.php';
