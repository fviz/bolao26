<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $this->get(route('ranking.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can visit the ranking page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('ranking.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('ranking/Index')
            ->has('leaderboard'));
});

test('ranking page shows competition ranks with ties', function () {
    $currentUser = User::factory()->create(['name' => 'Me', 'total_points' => 200]);
    User::factory()->create(['name' => 'Other', 'total_points' => 200]);
    User::factory()->create(['name' => 'Last', 'total_points' => 100]);

    $this->actingAs($currentUser)
        ->get(route('ranking.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('leaderboard', 3)
            ->where('leaderboard.0.rank', 1)
            ->where('leaderboard.1.rank', 1)
            ->where('leaderboard.2.rank', 3)
            ->where('leaderboard.0.isCurrentUser', true)
            ->where('leaderboard.1.isCurrentUser', false));
});
