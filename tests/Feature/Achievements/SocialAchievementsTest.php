<?php

use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use App\Services\Scoring\ScoreGamePredictions;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('lobo solitario awards only user with exact score', function () {
    $winner = User::factory()->create();
    $other = User::factory()->create();
    $game = Game::factory()->finished(['home_score' => 2, 'away_score' => 1])->create();

    Prediction::factory()->create([
        'user_id' => $winner->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
    ]);

    Prediction::factory()->create([
        'user_id' => $other->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 0,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    expect(userHasAchievement($winner->fresh(), 'lobo-solitario'))->toBeTrue()
        ->and(userHasAchievement($other->fresh(), 'lobo-solitario'))->toBeFalse();
});

test('contra a correnteza awards underdog winner pick', function () {
    $underdogPicker = User::factory()->create();
    $majorityPickers = User::factory()->count(5)->create();
    $game = Game::factory()->finished(['home_score' => 0, 'away_score' => 1])->create();

    Prediction::factory()->create([
        'user_id' => $underdogPicker->id,
        'game_id' => $game->id,
        'home_score' => 0,
        'away_score' => 1,
    ]);

    foreach ($majorityPickers as $user) {
        Prediction::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'home_score' => 2,
            'away_score' => 0,
        ]);
    }

    app(ScoreGamePredictions::class)->score($game->fresh());

    expect(userHasAchievement($underdogPicker->fresh(), 'contra-a-correnteza'))->toBeTrue();
});

test('zicou o bonde awards everyone when unanimous favorite loses', function () {
    $users = User::factory()->count(3)->create();
    $game = Game::factory()->finished(['home_score' => 0, 'away_score' => 1])->create();

    foreach ($users as $user) {
        Prediction::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'home_score' => 2,
            'away_score' => 0,
        ]);
    }

    app(ScoreGamePredictions::class)->score($game->fresh());

    foreach ($users as $user) {
        expect(userHasAchievement($user->fresh(), 'zicou-o-bonde'))->toBeTrue();
    }
});
