<?php

use App\Contracts\TournamentTopScorerResolver;
use App\Models\TopScorerPrediction;
use App\Models\User;
use App\Services\Scoring\ScoreTopScorerPredictions;
use App\Services\TournamentTopScorer\NullTournamentTopScorerResolver;
use App\Support\WorldCupPlayers;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

const TOP_SCORER_PLAYER_ID = 'norway-erling-haaland';

test('world cup players registry is non-empty with unique ids', function () {
    $players = WorldCupPlayers::all();

    expect($players)->not->toBeEmpty()
        ->and(count($players))->toBe(count(WorldCupPlayers::ids()))
        ->and(WorldCupPlayers::find(TOP_SCORER_PLAYER_ID))->not->toBeNull();
});

test('tournament top scorer resolver stub returns null', function () {
    expect(app(TournamentTopScorerResolver::class))->toBeInstanceOf(NullTournamentTopScorerResolver::class)
        ->and(app(TournamentTopScorerResolver::class)->resolve())->toBeNull();
});

test('user can save top scorer prediction before deadline', function () {
    Config::set('bolao.top_scorer_predictions_deadline', now()->addDay()->toDateTimeString());

    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('top-scorer-prediction.upsert'), [
            'player_id' => TOP_SCORER_PLAYER_ID,
        ])
        ->assertRedirect(route('predictions.index'));

    expect($user->fresh()->topScorerPrediction?->player_id)->toBe(TOP_SCORER_PLAYER_ID);
});

test('user can remove top scorer prediction before deadline', function () {
    Config::set('bolao.top_scorer_predictions_deadline', now()->addDay()->toDateTimeString());

    $user = User::factory()->create();

    TopScorerPrediction::factory()->create([
        'user_id' => $user->id,
        'player_id' => TOP_SCORER_PLAYER_ID,
    ]);

    $this->actingAs($user)
        ->delete(route('top-scorer-prediction.destroy'))
        ->assertRedirect(route('predictions.index'));

    expect($user->fresh()->topScorerPrediction)->toBeNull();
});

test('top scorer prediction removal rejected after deadline', function () {
    Config::set('bolao.top_scorer_predictions_deadline', now()->subMinute()->toDateTimeString());

    $user = User::factory()->create();

    TopScorerPrediction::factory()->create([
        'user_id' => $user->id,
        'player_id' => TOP_SCORER_PLAYER_ID,
    ]);

    $this->actingAs($user)
        ->delete(route('top-scorer-prediction.destroy'))
        ->assertSessionHasErrors('player_id');
});

test('top scorer prediction rejected after deadline', function () {
    Config::set('bolao.top_scorer_predictions_deadline', now()->subMinute()->toDateTimeString());

    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('top-scorer-prediction.upsert'), [
            'player_id' => TOP_SCORER_PLAYER_ID,
        ])
        ->assertSessionHasErrors('player_id');
});

test('top scorer prediction rejected for invalid player id', function () {
    Config::set('bolao.top_scorer_predictions_deadline', now()->addDay()->toDateTimeString());

    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('top-scorer-prediction.upsert'), [
            'player_id' => 'invalid-player-id',
        ])
        ->assertSessionHasErrors('player_id');
});

test('scores top scorer predictions when player id is provided', function () {
    $correctUser = User::factory()->create();
    $wrongUser = User::factory()->create();

    TopScorerPrediction::factory()->create([
        'user_id' => $correctUser->id,
        'player_id' => TOP_SCORER_PLAYER_ID,
    ]);

    TopScorerPrediction::factory()->create([
        'user_id' => $wrongUser->id,
        'player_id' => 'brazil-neymar',
    ]);

    expect(app(ScoreTopScorerPredictions::class)->score(TOP_SCORER_PLAYER_ID))->toBeTrue()
        ->and($correctUser->fresh()->total_points)->toBe(300)
        ->and($wrongUser->fresh()->total_points)->toBe(0)
        ->and($correctUser->topScorerPrediction?->points)->toBe(300);
});

test('score without player id returns false when resolver is stub', function () {
    $user = User::factory()->create();

    TopScorerPrediction::factory()->create([
        'user_id' => $user->id,
        'player_id' => TOP_SCORER_PLAYER_ID,
    ]);

    expect(app(ScoreTopScorerPredictions::class)->score())->toBeFalse()
        ->and($user->fresh()->total_points)->toBe(0);
});

test('bolao score top scorer command requires player when resolver is stub', function () {
    $this->artisan('bolao:score-top-scorer')
        ->assertFailed();
});

test('bolao score top scorer command scores with player option', function () {
    $user = User::factory()->create();

    TopScorerPrediction::factory()->create([
        'user_id' => $user->id,
        'player_id' => TOP_SCORER_PLAYER_ID,
    ]);

    $this->artisan('bolao:score-top-scorer', ['--player' => TOP_SCORER_PLAYER_ID])
        ->assertSuccessful();

    expect($user->fresh()->total_points)->toBe(300);
});
