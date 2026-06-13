<?php

use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use App\Services\Scoring\ScoreGamePredictions;
use App\Support\PenaltyWinner;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('exact score awards na gaveta and saindo do zero', function () {
    $user = User::factory()->create(['total_points' => 0]);
    $game = Game::factory()->finished(['home_score' => 2, 'away_score' => 1])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    expect(userHasAchievement($user->fresh(), 'na-gaveta'))->toBeTrue()
        ->and(userHasAchievement($user->fresh(), 'saindo-do-zero'))->toBeTrue();
});

test('draw awards diplomata', function () {
    $user = User::factory()->create();
    $game = Game::factory()->finished(['home_score' => 1, 'away_score' => 1])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 2,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    expect(userHasAchievement($user->fresh(), 'diplomata'))->toBeTrue();
});

test('winner with one exact team score awards mae dinah', function () {
    $user = User::factory()->create();
    $game = Game::factory()->finished(['home_score' => 3, 'away_score' => 1])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    expect(userHasAchievement($user->fresh(), 'mae-dinah'))->toBeTrue();
});

test('near miss by one total goal awards no quase', function () {
    $user = User::factory()->create();
    $game = Game::factory()->finished(['home_score' => 2, 'away_score' => 2])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    expect(userHasAchievement($user->fresh(), 'no-quase'))->toBeTrue();
});

test('mirror score awards efeito espelho', function () {
    $user = User::factory()->create();
    $game = Game::factory()->finished(['home_score' => 1, 'away_score' => 2])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    expect(userHasAchievement($user->fresh(), 'efeito-espelho'))->toBeTrue();
});

test('zero zero prediction in high scoring game awards inocente', function () {
    $user = User::factory()->create();
    $game = Game::factory()->finished(['home_score' => 3, 'away_score' => 3])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 0,
        'away_score' => 0,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    expect(userHasAchievement($user->fresh(), 'inocente'))->toBeTrue();
});

test('penalty perfection awards frieza total and coracao de aco', function () {
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

    expect(userHasAchievement($user->fresh(), 'frieza-total'))->toBeTrue()
        ->and(userHasAchievement($user->fresh(), 'coracao-de-aco'))->toBeTrue();
});

test('wrong penalty winner awards morreu na praia', function () {
    $user = User::factory()->create();
    $game = Game::factory()->knockout()->finished([
        'home_score' => 1,
        'away_score' => 1,
        'home_penalty_score' => 4,
        'away_penalty_score' => 3,
        'penalty_winner' => PenaltyWinner::Home,
    ])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 2,
        'penalty_winner' => PenaltyWinner::Away,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    expect(userHasAchievement($user->fresh(), 'morreu-na-praia'))->toBeTrue();
});

test('scoring streak awards no embalo and hat trick', function () {
    $user = User::factory()->create();

    $games = collect([
        ['home_score' => 1, 'away_score' => 0, 'pred_home' => 1, 'pred_away' => 0, 'points' => 75],
        ['home_score' => 2, 'away_score' => 1, 'pred_home' => 2, 'pred_away' => 1, 'points' => 200],
        ['home_score' => 0, 'away_score' => 1, 'pred_home' => 0, 'pred_away' => 1, 'points' => 75],
    ])->map(function (array $data, int $index) use ($user) {
        $game = Game::factory()->finished([
            'home_score' => $data['home_score'],
            'away_score' => $data['away_score'],
            'scheduled_at' => now()->subDays(3 - $index),
            'local_scheduled_at' => now()->subDays(3 - $index),
        ])->create();

        Prediction::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'home_score' => $data['pred_home'],
            'away_score' => $data['pred_away'],
        ]);

        app(ScoreGamePredictions::class)->score($game->fresh());

        return $game;
    });

    expect(userHasAchievement($user->fresh(), 'no-embalo'))->toBeTrue()
        ->and(userHasAchievement($user->fresh(), 'hat-trick'))->toBeTrue();
});

test('achievements are not awarded twice', function () {
    $user = User::factory()->create();
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

    expect($user->fresh()->userAchievements()
        ->whereHas('achievement', fn ($query) => $query->where('slug', 'na-gaveta'))
        ->count())->toBe(1);
});
