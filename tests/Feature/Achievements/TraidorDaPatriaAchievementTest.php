<?php

use App\Models\Achievement;
use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use App\Models\UserAchievement;
use App\Services\Scoring\ScoreGamePredictions;
use App\Support\PenaltyWinner;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function brazilGame(array $overrides = []): Game
{
    return Game::factory()->finished(array_merge([
        'home_name' => 'Brazil',
        'home_abbr' => 'BRA',
        'away_name' => 'France',
        'away_abbr' => 'FRA',
        'home_score' => 0,
        'away_score' => 1,
    ], $overrides))->create();
}

test('awards traidor da patria when brazil home loses and user predicted away win', function () {
    $user = User::factory()->create();
    $game = brazilGame();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 0,
        'away_score' => 1,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    expect(userHasAchievement($user->fresh(), 'traidor-da-patria'))->toBeTrue();
});

test('awards traidor da patria when brazil away loses and user predicted home win', function () {
    $user = User::factory()->create();
    $game = Game::factory()->finished([
        'home_name' => 'France',
        'home_abbr' => 'FRA',
        'away_name' => 'Brazil',
        'away_abbr' => 'BRA',
        'home_score' => 2,
        'away_score' => 0,
    ])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 0,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    expect(userHasAchievement($user->fresh(), 'traidor-da-patria'))->toBeTrue();
});

test('awards traidor da patria on knockout penalty loss prediction', function () {
    $user = User::factory()->create();
    $game = Game::factory()->knockout()->finished([
        'home_name' => 'Brazil',
        'home_abbr' => 'BRA',
        'away_name' => 'France',
        'away_abbr' => 'FRA',
        'home_score' => 1,
        'away_score' => 1,
        'home_penalty_score' => 3,
        'away_penalty_score' => 4,
        'penalty_winner' => PenaltyWinner::Away,
    ])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 1,
        'away_score' => 1,
        'penalty_winner' => PenaltyWinner::Away,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    expect(userHasAchievement($user->fresh(), 'traidor-da-patria'))->toBeTrue();
});

test('does not award when user predicted brazil win but brazil lost', function () {
    $user = User::factory()->create();
    $game = brazilGame();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 0,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    expect(userHasAchievement($user->fresh(), 'traidor-da-patria'))->toBeFalse();
});

test('does not award when user predicted brazil loss but brazil won', function () {
    $user = User::factory()->create();
    $game = Game::factory()->finished([
        'home_name' => 'Brazil',
        'home_abbr' => 'BRA',
        'away_name' => 'France',
        'away_abbr' => 'FRA',
        'home_score' => 2,
        'away_score' => 0,
    ])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 0,
        'away_score' => 1,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    expect(userHasAchievement($user->fresh(), 'traidor-da-patria'))->toBeFalse();
});

test('does not award for games without brazil', function () {
    $user = User::factory()->create();
    $game = Game::factory()->finished([
        'home_name' => 'France',
        'home_abbr' => 'FRA',
        'away_name' => 'Argentina',
        'away_abbr' => 'ARG',
        'home_score' => 0,
        'away_score' => 1,
    ])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 0,
        'away_score' => 1,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    expect(userHasAchievement($user->fresh(), 'traidor-da-patria'))->toBeFalse();
});

test('does not award when user predicted draw without penalty pick and brazil lost', function () {
    $user = User::factory()->create();
    $game = brazilGame();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 1,
        'away_score' => 1,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    expect(userHasAchievement($user->fresh(), 'traidor-da-patria'))->toBeFalse();
});

test('traidor da patria award is idempotent', function () {
    $user = User::factory()->create();
    $game = brazilGame();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 0,
        'away_score' => 1,
    ]);

    $scorer = app(ScoreGamePredictions::class);
    $scorer->score($game->fresh());
    $scorer->score($game->fresh());

    expect($user->fresh()->userAchievements()
        ->whereHas('achievement', fn ($query) => $query->where('slug', 'traidor-da-patria'))
        ->count())->toBe(1);
});

test('traidor da patria auto sets featured achievement', function () {
    $user = User::factory()->create();
    $otherAchievement = Achievement::query()->where('slug', 'primeiro-chute')->firstOrFail();

    UserAchievement::query()->create([
        'user_id' => $user->id,
        'achievement_id' => $otherAchievement->id,
        'awarded_at' => now(),
    ]);

    $user->featured_achievement_id = $otherAchievement->id;
    $user->save();

    $game = brazilGame();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 0,
        'away_score' => 1,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    $traidor = Achievement::query()->where('slug', 'traidor-da-patria')->firstOrFail();

    expect($user->fresh()->featured_achievement_id)->toBe($traidor->id);
});

test('traidor da patria medal uses lixo humano tier', function () {
    $achievement = Achievement::query()->where('slug', 'traidor-da-patria')->firstOrFail();

    expect($achievement->tier->value)->toBe('lixo_humano')
        ->and($achievement->tier->label())->toBe('Lixo Humano')
        ->and($achievement->emoji)->toBe('💩');
});
