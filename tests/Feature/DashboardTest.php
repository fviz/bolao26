<?php

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard lists only upcoming games paginated twenty per page', function () {
    $user = User::factory()->create();

    Game::factory()->count(21)->create([
        'scheduled_at' => now()->addDay(),
    ]);

    Game::factory()->past()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('games.data', 20)
            ->where('games.meta.total', 21)
            ->where('games.meta.last_page', 2));

    $this->actingAs($user)
        ->get(route('dashboard', ['page' => 2]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('games.data', 1));
});
