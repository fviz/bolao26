<?php

use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use App\Notifications\AchievementEarned;
use App\Services\Scoring\ScoreGamePredictions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('earning achievements sends one batched notification', function () {
    $user = User::factory()->create(['total_points' => 0]);
    $game = Game::factory()->finished(['home_score' => 2, 'away_score' => 1])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    $achievementNotifications = $user->fresh()->notifications()
        ->where('data->type', 'achievement_earned')
        ->get();

    expect($achievementNotifications)->toHaveCount(1)
        ->and($achievementNotifications->first()->data['body'])->toContain('Você ganhou a medalha')
        ->and($achievementNotifications->first()->data['body'])->toContain('e mais');
});

test('achievement notification is queued', function () {
    Notification::fake();

    $user = User::factory()->create(['total_points' => 0]);
    $game = Game::factory()->finished(['home_score' => 2, 'away_score' => 1])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    Notification::assertSentTo($user, AchievementEarned::class);
});

test('scoring the same game twice does not duplicate achievement notifications', function () {
    $user = User::factory()->create(['total_points' => 0]);
    $game = Game::factory()->finished(['home_score' => 2, 'away_score' => 1])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
    ]);

    $scorer = app(ScoreGamePredictions::class);
    $scorer->score($game->fresh());
    $scorer->score($game->fresh());

    expect($user->fresh()->notifications()
        ->where('data->type', 'achievement_earned')
        ->count())->toBe(1);
});
