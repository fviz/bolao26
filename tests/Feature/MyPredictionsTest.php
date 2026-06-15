<?php

use App\Models\Game;
use App\Models\GameComment;
use App\Models\Prediction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login', function () {
    $this->get(route('predictions.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view my predictions page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('predictions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('predictions/Index')
            ->has('predictedGames.data')
            ->has('missingGames.data')
            ->has('championTeams')
            ->has('players')
            ->missing('nextGame'));
});

test('predicted games section lists only games with user predictions', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $predicted = Game::factory()->create([
        'scheduled_at' => now()->addDay(),
    ]);
    $notPredicted = Game::factory()->create([
        'scheduled_at' => now()->addDays(2),
    ]);
    $otherUserGame = Game::factory()->create([
        'scheduled_at' => now()->addDays(3),
    ]);

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $predicted->id,
        'home_score' => 2,
        'away_score' => 1,
    ]);

    Prediction::factory()->create([
        'user_id' => $otherUser->id,
        'game_id' => $otherUserGame->id,
    ]);

    GameComment::factory()->count(3)->for($predicted)->create();
    GameComment::factory()->for($notPredicted)->create();

    $this->actingAs($user)
        ->get(route('predictions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('predictedGames.data', 1)
            ->where('predictedGames.data.0.id', $predicted->id)
            ->where('predictedGames.data.0.commentsCount', 3)
            ->where('predictedGames.data.0.userPrediction.homeScore', 2)
            ->where('predictedGames.data.0.userPrediction.awayScore', 1)
            ->where('missingGames.data.0.commentsCount', 1)
            ->where('missingGames.data', fn ($games) => collect($games)->pluck('id')->contains($notPredicted->id)
                && ! collect($games)->pluck('id')->contains($predicted->id)));
});

test('missing games section lists only betting open games without user prediction', function () {
    $user = User::factory()->create();

    $missing = Game::factory()->create([
        'scheduled_at' => now()->addHours(2),
    ]);
    $predicted = Game::factory()->create([
        'scheduled_at' => now()->addHours(3),
    ]);
    $bettingClosed = Game::factory()->create([
        'scheduled_at' => now()->subHour(),
    ]);

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $predicted->id,
    ]);

    $this->actingAs($user)
        ->get(route('predictions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('missingGames.data', 1)
            ->where('missingGames.data.0.id', $missing->id)
            ->where('missingGames.data.0.isBettingOpen', true)
            ->where('missingGames.data', fn ($games) => ! collect($games)->pluck('id')->contains($predicted->id)
                && ! collect($games)->pluck('id')->contains($bettingClosed->id)));
});

test('predicted games section exposes result and points for finished games', function () {
    $user = User::factory()->create();

    $finished = Game::factory()->finished([
        'home_score' => 2,
        'away_score' => 1,
    ])->create([
        'scheduled_at' => now()->subDay(),
    ]);

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $finished->id,
        'home_score' => 2,
        'away_score' => 1,
        'points' => 200,
    ]);

    $this->actingAs($user)
        ->get(route('predictions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('predictedGames.data', 1)
            ->where('predictedGames.data.0.isFinal', true)
            ->where('predictedGames.data.0.result.homeScore', 2)
            ->where('predictedGames.data.0.result.awayScore', 1)
            ->where('predictedGames.data.0.userPrediction.points', 200));
});

test('predicted games are paginated twenty per page', function () {
    $user = User::factory()->create();

    $games = Game::factory()->count(21)->create([
        'scheduled_at' => now()->addDay(),
    ]);

    foreach ($games as $game) {
        Prediction::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
        ]);
    }

    $this->actingAs($user)
        ->get(route('predictions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('predictedGames.data', 20)
            ->where('predictedGames.meta.total', 21)
            ->where('predictedGames.meta.last_page', 2));

    $this->actingAs($user)
        ->get(route('predictions.index', ['predicted_page' => 2]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('predictedGames.data', 1));
});
