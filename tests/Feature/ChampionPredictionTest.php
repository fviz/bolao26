<?php

use App\Models\ChampionPrediction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
use App\Models\Game;
use App\Models\User;
use App\Services\Scoring\ScoreChampionPredictions;
use App\Support\PenaltyWinner;
use Illuminate\Support\Facades\Config;

test('user can save champion prediction before deadline', function () {
    Config::set('bolao.champion_predictions_deadline', now()->addDay()->toDateTimeString());

    $user = User::factory()->create();
    $game = Game::factory()->create([
        'home_fifa_team_id' => 'team-home',
        'home_name' => 'Brazil',
        'home_abbr' => 'BRA',
    ]);

    $this->actingAs($user)
        ->put(route('champion-prediction.upsert'), [
            'fifa_team_id' => $game->home_fifa_team_id,
        ])
        ->assertRedirect(route('predictions.index'));

    expect($user->fresh()->championPrediction?->fifa_team_id)->toBe('team-home');
});

test('user can remove champion prediction before deadline', function () {
    Config::set('bolao.champion_predictions_deadline', now()->addDay()->toDateTimeString());

    $user = User::factory()->create();
    $game = Game::factory()->create([
        'home_fifa_team_id' => 'team-home',
        'home_name' => 'Brazil',
    ]);

    ChampionPrediction::factory()->create([
        'user_id' => $user->id,
        'fifa_team_id' => $game->home_fifa_team_id,
    ]);

    $this->actingAs($user)
        ->delete(route('champion-prediction.destroy'))
        ->assertRedirect(route('predictions.index'));

    expect($user->fresh()->championPrediction)->toBeNull();
});

test('champion prediction removal rejected after deadline', function () {
    Config::set('bolao.champion_predictions_deadline', now()->subMinute()->toDateTimeString());

    $user = User::factory()->create();

    ChampionPrediction::factory()->create([
        'user_id' => $user->id,
        'fifa_team_id' => 'team-home',
    ]);

    $this->actingAs($user)
        ->delete(route('champion-prediction.destroy'))
        ->assertSessionHasErrors('fifa_team_id');
});

test('champion prediction rejected after deadline', function () {
    Config::set('bolao.champion_predictions_deadline', now()->subMinute()->toDateTimeString());

    $user = User::factory()->create();
    $game = Game::factory()->create([
        'home_fifa_team_id' => 'team-home',
        'home_name' => 'Brazil',
    ]);

    $this->actingAs($user)
        ->put(route('champion-prediction.upsert'), [
            'fifa_team_id' => $game->home_fifa_team_id,
        ])
        ->assertSessionHasErrors('fifa_team_id');
});

test('scores champion predictions when final match completes', function () {
    $winnerId = 'winner-team';

    $final = Game::factory()->finalMatch()->finished([
        'home_score' => 2,
        'away_score' => 1,
        'home_fifa_team_id' => $winnerId,
        'home_name' => 'Winnerland',
    ])->create();

    $correctUser = User::factory()->create();
    $wrongUser = User::factory()->create();

    ChampionPrediction::factory()->create([
        'user_id' => $correctUser->id,
        'fifa_team_id' => $winnerId,
    ]);

    ChampionPrediction::factory()->create([
        'user_id' => $wrongUser->id,
        'fifa_team_id' => 'other-team',
    ]);

    app(ScoreChampionPredictions::class)->scoreForFinal($final);

    expect($correctUser->fresh()->total_points)->toBe(300)
        ->and($wrongUser->fresh()->total_points)->toBe(0)
        ->and($correctUser->championPrediction?->points)->toBe(300);
});

test('final with penalties uses penalty winner for champion', function () {
    $homeId = 'home-team';
    $awayId = 'away-team';

    $final = Game::factory()->finalMatch()->finished([
        'home_score' => 1,
        'away_score' => 1,
        'home_fifa_team_id' => $homeId,
        'away_fifa_team_id' => $awayId,
        'home_penalty_score' => 4,
        'away_penalty_score' => 5,
        'penalty_winner' => PenaltyWinner::Away,
    ])->create();

    $user = User::factory()->create();

    ChampionPrediction::factory()->create([
        'user_id' => $user->id,
        'fifa_team_id' => $awayId,
    ]);

    app(ScoreChampionPredictions::class)->scoreForFinal($final);

    expect($user->fresh()->total_points)->toBe(300);
});
