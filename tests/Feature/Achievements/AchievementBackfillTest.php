<?php

use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use App\Services\Achievements\AchievementBackfiller;
use App\Services\Scoring\ScoreGamePredictions;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('backfill awards achievements for already scored games without notifications', function () {
    $user = User::factory()->create(['total_points' => 200]);
    $game = Game::factory()->finished([
        'home_score' => 2,
        'away_score' => 1,
        'scored_at' => now()->subDay(),
    ])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
        'points' => 200,
        'scored_at' => now()->subDay(),
    ]);

    app(AchievementBackfiller::class)->backfill($user);

    expect(userHasAchievement($user->fresh(), 'na-gaveta'))->toBeTrue();

    expect($user->fresh()->notifications()->where('data->type', 'achievement_earned')->count())->toBe(0);
});

test('backfill uses historical awarded at timestamps', function () {
    $user = User::factory()->create(['total_points' => 200]);
    $scoredAt = now()->subDays(3);
    $game = Game::factory()->finished([
        'home_score' => 2,
        'away_score' => 1,
        'scored_at' => $scoredAt,
    ])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
        'points' => 200,
        'scored_at' => $scoredAt,
    ]);

    app(AchievementBackfiller::class)->backfill($user);

    $award = $user->fresh()->userAchievements()
        ->whereHas('achievement', fn ($query) => $query->where('slug', 'na-gaveta'))
        ->first();

    expect($award?->awarded_at?->toDateTimeString())->toBe($scoredAt->toDateTimeString());
});

test('backfill command reports newly awarded medals', function () {
    $user = User::factory()->create(['total_points' => 200]);
    $game = Game::factory()->finished(['home_score' => 2, 'away_score' => 1])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
        'points' => 200,
        'scored_at' => now(),
    ]);

    $this->artisan('achievements:backfill')
        ->assertSuccessful()
        ->expectsOutputToContain('new medal(s) awarded');
});

test('scoring without prior achievement evaluation does not duplicate medals on backfill', function () {
    $user = User::factory()->create();
    $game = Game::factory()->finished(['home_score' => 2, 'away_score' => 1])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());
    app(AchievementBackfiller::class)->backfill($user);

    expect($user->fresh()->userAchievements()
        ->whereHas('achievement', fn ($query) => $query->where('slug', 'na-gaveta'))
        ->count())->toBe(1);
});
