<?php

use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot view the games index', function () {
    $this->get(route('games.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view all games from first to last', function () {
    $user = User::factory()->create();

    $firstGame = Game::factory()->finished([
        'home_score' => 1,
        'away_score' => 0,
    ])->create([
        'scheduled_at' => now()->subDays(2),
        'home_name' => 'Brazil',
        'away_name' => 'France',
    ]);

    $secondGame = Game::factory()->create([
        'scheduled_at' => now()->addDay(),
        'home_name' => 'Argentina',
        'away_name' => 'Germany',
    ]);

    $this->actingAs($user)
        ->get(route('games.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('games/Index')
            ->has('games.data', 2)
            ->where('games.data.0.id', $firstGame->id)
            ->where('games.data.0.result.homeScore', 1)
            ->where('games.data.0.result.awayScore', 0)
            ->where('games.data.1.id', $secondGame->id)
            ->missing('games.data.1.result'));
});

test('games index exposes user prediction and paginates twelve per page', function () {
    $user = User::factory()->create();

    $games = Game::factory()->count(13)->create([
        'scheduled_at' => now()->addDay(),
    ]);

    Prediction::factory()->for($user)->for($games->first())->create([
        'home_score' => 3,
        'away_score' => 1,
    ]);

    $this->actingAs($user)
        ->get(route('games.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('games.data', 12)
            ->where('games.meta.total', 13)
            ->where('games.meta.last_page', 2)
            ->where('games.data.0.userPrediction.homeScore', 3)
            ->where('games.data.0.userPrediction.awayScore', 1));

    $this->actingAs($user)
        ->get(route('games.index', ['page' => 2]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('games.data', 1));
});
