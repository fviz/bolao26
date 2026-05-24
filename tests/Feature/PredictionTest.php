<?php

use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('user can save a prediction while betting is open', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create([
        'scheduled_at' => now()->addHours(2),
    ]);

    $this->actingAs($user)
        ->put(route('games.prediction.upsert', $game), [
            'home_score' => 2,
            'away_score' => 1,
        ])
        ->assertRedirect(route('games.show', $game));

    $prediction = Prediction::query()
        ->where('user_id', $user->id)
        ->where('game_id', $game->id)
        ->first();

    expect($prediction)->not->toBeNull()
        ->and($prediction->home_score)->toBe(2)
        ->and($prediction->away_score)->toBe(1);
});

test('user can update an existing prediction', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create([
        'scheduled_at' => now()->addHours(2),
    ]);

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 0,
        'away_score' => 0,
    ]);

    $this->actingAs($user)
        ->put(route('games.prediction.upsert', $game), [
            'home_score' => 3,
            'away_score' => 2,
        ])
        ->assertRedirect(route('games.show', $game));

    expect(Prediction::query()->where('game_id', $game->id)->count())->toBe(1)
        ->and($game->fresh()->userPrediction($user)?->home_score)->toBe(3);
});

test('prediction is rejected less than one minute before kickoff', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create([
        'scheduled_at' => now()->addMinutes(10),
    ]);

    Carbon::setTestNow($game->scheduled_at->copy()->subSeconds(30));

    $this->actingAs($user)
        ->put(route('games.prediction.upsert', $game), [
            'home_score' => 1,
            'away_score' => 0,
        ])
        ->assertSessionHasErrors('home_score');

    expect(Prediction::query()->count())->toBe(0);

    Carbon::setTestNow();
});

test('prediction is accepted more than one minute before kickoff', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create([
        'scheduled_at' => now()->addMinutes(10),
    ]);

    Carbon::setTestNow($game->scheduled_at->copy()->subMinutes(2));

    $this->actingAs($user)
        ->put(route('games.prediction.upsert', $game), [
            'home_score' => 1,
            'away_score' => 0,
        ])
        ->assertRedirect(route('games.show', $game));

    expect(Prediction::query()->count())->toBe(1);

    Carbon::setTestNow();
});

test('knockout draw prediction requires penalty winner', function () {
    $user = User::factory()->create();
    $game = Game::factory()->knockout()->create([
        'scheduled_at' => now()->addHours(2),
    ]);

    $this->actingAs($user)
        ->put(route('games.prediction.upsert', $game), [
            'home_score' => 2,
            'away_score' => 2,
        ])
        ->assertSessionHasErrors('penalty_winner');
});

test('knockout draw prediction saves penalty winner', function () {
    $user = User::factory()->create();
    $game = Game::factory()->knockout()->create([
        'scheduled_at' => now()->addHours(2),
    ]);

    $this->actingAs($user)
        ->put(route('games.prediction.upsert', $game), [
            'home_score' => 2,
            'away_score' => 2,
            'penalty_winner' => 'home',
        ])
        ->assertRedirect(route('games.show', $game));

    expect($game->fresh()->userPrediction($user)?->penalty_winner)->toBe('home');
});
