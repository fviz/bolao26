<?php

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
use App\Models\Prediction;
use App\Models\User;
use App\Services\Scoring\ScoreGamePredictions;
use App\Support\PenaltyWinner;

test('scores predictions when game is finished', function () {
    $user = User::factory()->create();
    $game = Game::factory()->finished(['home_score' => 2, 'away_score' => 1])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    $user->refresh();
    $prediction = $user->predictions()->first();

    expect($prediction->points)->toBe(200)
        ->and($user->total_points)->toBe(200)
        ->and($game->fresh()->scored_at)->not->toBeNull();
});

test('rescoring updates user total by delta', function () {
    $user = User::factory()->create(['total_points' => 200]);
    $game = Game::factory()->finished(['home_score' => 2, 'away_score' => 1])->create();

    $prediction = Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
        'points' => 200,
        'scored_at' => now(),
    ]);

    $game->update(['home_score' => 3, 'away_score' => 1]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    $user->refresh();
    $prediction->refresh();

    expect($prediction->points)->toBe(95)
        ->and($user->total_points)->toBe(95);
});

test('knockout penalty draw scores 220 points', function () {
    $user = User::factory()->create();
    $game = Game::factory()->knockout()->finished([
        'home_score' => 2,
        'away_score' => 2,
        'home_penalty_score' => 3,
        'away_penalty_score' => 4,
        'penalty_winner' => PenaltyWinner::Away,
    ])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 2,
        'penalty_winner' => PenaltyWinner::Away,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    expect($user->fresh()->total_points)->toBe(220);
});

test('bolao score games command scores final games', function () {
    $user = User::factory()->create();
    $game = Game::factory()->finished()->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
    ]);

    $this->artisan('bolao:score-games', ['--game' => $game->id])
        ->assertSuccessful();

    expect($user->fresh()->total_points)->toBe(200);
});

test('unscore removes awarded points from users', function () {
    $user = User::factory()->create(['total_points' => 75]);
    $game = Game::factory()->finished([
        'home_score' => 1,
        'away_score' => 0,
        'match_status' => 3,
        'scored_at' => now(),
    ])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
        'points' => 75,
        'scored_at' => now(),
    ]);

    app(ScoreGamePredictions::class)->unscore($game->fresh());

    $user->refresh();
    $prediction = $user->predictions()->first();

    expect($user->total_points)->toBe(0)
        ->and($prediction->points)->toBeNull()
        ->and($prediction->scored_at)->toBeNull()
        ->and($game->fresh()->scored_at)->toBeNull();
});
