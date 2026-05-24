<?php

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
            ->where('game.userPrediction', null));
});
