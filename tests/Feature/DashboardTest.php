<?php

use App\Models\Game;
use App\Models\GameComment;
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

test('dashboard exposes next upcoming game', function () {
    $user = User::factory()->create();

    $soonest = Game::factory()->create([
        'scheduled_at' => now()->addDay(),
        'home_name' => 'Brazil',
        'away_name' => 'France',
    ]);

    Game::factory()->create([
        'scheduled_at' => now()->addDays(2),
    ]);

    GameComment::factory()->count(2)->for($soonest)->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('nextGame.id', $soonest->id)
            ->where('nextGame.matchTitle', 'Brazil x France')
            ->where('nextGame.commentsCount', 2)
            ->where('games.data.0.commentsCount', 2)
            ->missing('championPrediction'));
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

test('dashboard leaderboard widget centers on the current user', function () {
    foreach (range(1, 12) as $position) {
        User::factory()->create([
            'name' => "User {$position}",
            'total_points' => 1200 - ($position * 10),
        ]);
    }

    $tenthUser = User::query()->where('name', 'User 10')->firstOrFail();

    $this->actingAs($tenthUser)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('leaderboard', 5)
            ->where('leaderboard.0.rank', 8)
            ->where('leaderboard.2.rank', 10)
            ->where('leaderboard.4.rank', 12)
            ->where('leaderboard.2.isCurrentUser', true)
            ->where('leaderboard.2.name', 'User 10'));
});

test('dashboard leaderboard widget assigns tied ranks', function () {
    $user = User::factory()->create(['name' => 'Me', 'total_points' => 200]);
    User::factory()->create(['name' => 'Other', 'total_points' => 200]);
    User::factory()->create(['name' => 'Last', 'total_points' => 100]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('leaderboard', 3)
            ->where('leaderboard.0.rank', 1)
            ->where('leaderboard.1.rank', 1)
            ->where('leaderboard.2.rank', 3));
});

test('dashboard exposes browser push availability when vapid keys are configured', function () {
    config([
        'webpush.vapid.public_key' => 'public-key',
        'webpush.vapid.private_key' => 'private-key',
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('browserPushAvailable', true));
});

test('dashboard exposes browser push as unavailable when vapid keys are missing', function () {
    config([
        'webpush.vapid.public_key' => null,
        'webpush.vapid.private_key' => null,
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('browserPushAvailable', false));
});
