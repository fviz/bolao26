<?php

use App\Models\Achievement;
use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use App\Services\Achievements\Support\PredictionTiming;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('first prediction awards primeiro chute', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create([
        'scheduled_at' => now()->addDay(),
        'local_scheduled_at' => now()->addDay(),
    ]);

    $this->actingAs($user)
        ->put(route('games.prediction.upsert', $game), [
            'home_score' => 1,
            'away_score' => 0,
        ])
        ->assertRedirect();

    expect($user->fresh()->userAchievements()
        ->whereHas('achievement', fn ($query) => $query->where('slug', 'primeiro-chute'))
        ->exists())->toBeTrue();
});

test('predicting all group stage games awards gabaritando a agenda', function () {
    $user = User::factory()->create();

    $games = Game::factory()->count(2)->create([
        'id_group' => '289275',
        'scheduled_at' => now()->addDay(),
        'local_scheduled_at' => now()->addDay(),
    ]);

    foreach ($games as $game) {
        $this->actingAs($user)
            ->put(route('games.prediction.upsert', $game), [
                'home_score' => 1,
                'away_score' => 1,
            ])
            ->assertRedirect();
    }

    expect($user->fresh()->userAchievements()
        ->whereHas('achievement', fn ($query) => $query->where('slug', 'gabaritando-a-agenda'))
        ->exists())->toBeTrue();
});

test('gabaritando progress tracks group stage predictions', function () {
    $user = User::factory()->create();
    $achievement = Achievement::query()->where('slug', 'gabaritando-a-agenda')->firstOrFail();

    Game::factory()->count(3)->create([
        'id_group' => '289275',
        'scheduled_at' => now()->addDay(),
        'local_scheduled_at' => now()->addDay(),
    ]);

    $game = Game::factory()->create([
        'id_group' => '289275',
        'scheduled_at' => now()->addDays(2),
        'local_scheduled_at' => now()->addDays(2),
    ]);

    $this->actingAs($user)
        ->put(route('games.prediction.upsert', $game), [
            'home_score' => 2,
            'away_score' => 1,
        ])
        ->assertRedirect();

    $progress = $user->fresh()->achievementProgress()
        ->where('achievement_id', $achievement->id)
        ->first();

    expect($progress?->current_value)->toBe(1);
});

test('prediction within ten minutes of kickoff awards atrasado do enem', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create([
        'scheduled_at' => now()->addMinutes(10),
        'local_scheduled_at' => now()->addMinutes(10),
    ]);

    Carbon::setTestNow($game->scheduled_at->copy()->subMinutes(5));

    $this->actingAs($user)
        ->put(route('games.prediction.upsert', $game), [
            'home_score' => 1,
            'away_score' => 0,
        ])
        ->assertRedirect();

    expect(userHasAchievement($user->fresh(), 'atrasado-do-enem'))->toBeTrue();

    Carbon::setTestNow();
});

test('prediction more than ten minutes before kickoff does not award atrasado do enem', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create([
        'scheduled_at' => now()->addMinutes(10),
        'local_scheduled_at' => now()->addMinutes(10),
    ]);

    Carbon::setTestNow($game->scheduled_at->copy()->subMinutes(10));

    $this->actingAs($user)
        ->put(route('games.prediction.upsert', $game), [
            'home_score' => 1,
            'away_score' => 0,
        ])
        ->assertRedirect();

    expect(userHasAchievement($user->fresh(), 'atrasado-do-enem'))->toBeFalse();

    Carbon::setTestNow();
});

test('updating an early prediction close to kickoff awards atrasado do enem', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create([
        'scheduled_at' => now()->addDay(),
        'local_scheduled_at' => now()->addDay(),
    ]);

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 0,
        'away_score' => 0,
        'created_at' => now()->subHours(2),
    ]);

    Carbon::setTestNow($game->scheduled_at->copy()->subMinutes(2));

    $this->actingAs($user)
        ->put(route('games.prediction.upsert', $game), [
            'home_score' => 2,
            'away_score' => 1,
        ])
        ->assertRedirect();

    expect(userHasAchievement($user->fresh(), 'atrasado-do-enem'))->toBeTrue();

    Carbon::setTestNow();
});

test('prediction timing treats exactly ten minutes before kickoff as not late', function () {
    $game = Game::factory()->create([
        'scheduled_at' => now()->addMinutes(10),
        'local_scheduled_at' => now()->addMinutes(10),
    ]);

    $placedAt = $game->scheduled_at->copy()->subMinutes(PredictionTiming::LATE_BET_MINUTES);

    expect(PredictionTiming::isLateBet($game, $placedAt))->toBeFalse();
});
