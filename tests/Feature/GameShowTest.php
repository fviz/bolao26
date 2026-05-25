<?php

use App\Models\Game;
use App\Models\GameComment;
use App\Models\Prediction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('guests cannot view a game', function () {
    $game = Game::factory()->create();

    $this->get(route('games.show', $game))
        ->assertRedirect(route('login'));
});

test('authenticated users can view an upcoming game', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create([
        'scheduled_at' => now()->addDay(),
        'home_name' => 'Mexico',
        'away_name' => 'South Africa',
    ]);

    $this->actingAs($user)
        ->get(route('games.show', $game))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('games/Show')
            ->where('game.id', $game->id)
            ->where('game.matchTitle', 'Mexico x South Africa')
            ->where('game.isBettingOpen', true)
            ->where('game.arePredictionsVisible', false)
            ->where('game.userPrediction', null)
            ->has('game.allPredictions', 0)
            ->has('comments', 0));
});

test('other users predictions stay hidden while betting is open', function () {
    $viewer = User::factory()->create(['name' => 'Viewer']);
    $other = User::factory()->create(['name' => 'Other User']);
    $game = Game::factory()->create([
        'scheduled_at' => now()->addDay(),
    ]);

    Prediction::factory()->for($other)->for($game)->create([
        'home_score' => 3,
        'away_score' => 1,
    ]);

    Prediction::factory()->for($viewer)->for($game)->create([
        'home_score' => 2,
        'away_score' => 0,
    ]);

    $this->actingAs($viewer)
        ->get(route('games.show', $game))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('game.arePredictionsVisible', false)
            ->where('game.userPrediction.homeScore', 2)
            ->where('game.userPrediction.awayScore', 0)
            ->has('game.allPredictions', 2)
            ->where('game.allPredictions.0.userName', 'Other User')
            ->where('game.allPredictions.0.isCurrentUser', false)
            ->missing('game.allPredictions.0.homeScore')
            ->missing('game.allPredictions.0.awayScore')
            ->where('game.allPredictions.1.userName', 'Viewer')
            ->where('game.allPredictions.1.isCurrentUser', true)
            ->where('game.allPredictions.1.homeScore', 2)
            ->where('game.allPredictions.1.awayScore', 0));
});

test('all predictions are visible after betting closes', function () {
    $viewer = User::factory()->create(['name' => 'Viewer']);
    $other = User::factory()->create(['name' => 'Other User']);
    $game = Game::factory()->create([
        'scheduled_at' => now()->addSeconds(30),
    ]);

    Prediction::factory()->for($other)->for($game)->create([
        'home_score' => 3,
        'away_score' => 1,
    ]);

    Prediction::factory()->for($viewer)->for($game)->create([
        'home_score' => 2,
        'away_score' => 0,
    ]);

    $this->actingAs($viewer)
        ->get(route('games.show', $game))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('game.arePredictionsVisible', true)
            ->where('game.isBettingOpen', false)
            ->has('game.allPredictions', 2)
            ->where('game.allPredictions.0.userName', 'Other User')
            ->where('game.allPredictions.0.homeScore', 3)
            ->where('game.allPredictions.0.awayScore', 1)
            ->where('game.allPredictions.0.isCurrentUser', false)
            ->where('game.allPredictions.1.userName', 'Viewer')
            ->where('game.allPredictions.1.homeScore', 2)
            ->where('game.allPredictions.1.awayScore', 0)
            ->where('game.allPredictions.1.isCurrentUser', true));
});

test('game show includes comments ordered oldest to newest with replies nested', function () {
    $user = User::factory()->create(['name' => 'Viewer']);
    $replier = User::factory()->create(['name' => 'Replier']);
    $game = Game::factory()->create([
        'scheduled_at' => now()->addDay(),
    ]);

    $older = GameComment::factory()->for($game)->for($user)->create([
        'body' => 'Primeiro',
        'created_at' => now()->subMinutes(2),
    ]);

    $newer = GameComment::factory()->for($game)->for($user)->create([
        'body' => 'Segundo',
        'created_at' => now()->subMinute(),
    ]);

    GameComment::factory()->for($game)->for($replier)->replyTo($newer)->create([
        'body' => 'Resposta',
    ]);

    $this->actingAs($user)
        ->get(route('games.show', $game))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('comments', 2)
            ->where('comments.0.id', $older->id)
            ->where('comments.0.body', 'Primeiro')
            ->where('comments.0.isCurrentUser', true)
            ->has('comments.0.replies', 0)
            ->where('comments.1.id', $newer->id)
            ->where('comments.1.body', 'Segundo')
            ->has('comments.1.replies', 1)
            ->where('comments.1.replies.0.body', 'Resposta')
            ->where('comments.1.replies.0.userName', 'Replier')
            ->where('comments.1.replies.0.userAvatar', null)
            ->where('comments.1.replies.0.isCurrentUser', false));
});

test('game show comments include user avatar url when set', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $game = Game::factory()->create([
        'scheduled_at' => now()->addDay(),
    ]);

    $this->actingAs($user)
        ->post(route('profile.avatar.store'), [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

    $user->refresh();

    GameComment::factory()->for($game)->for($user)->create([
        'body' => 'Com avatar',
    ]);

    $this->actingAs($user)
        ->get(route('games.show', $game))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('comments', 1)
            ->where('comments.0.userAvatar', $user->avatar));
});

test('empty predictions list when betting is closed and nobody predicted', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create([
        'scheduled_at' => now()->addSeconds(30),
    ]);

    $this->actingAs($user)
        ->get(route('games.show', $game))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('game.arePredictionsVisible', true)
            ->has('game.allPredictions', 0)
            ->has('comments', 0));
});
