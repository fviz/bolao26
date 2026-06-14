<?php

use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $user = User::factory()->create();

    $this->get(route('users.show', $user))
        ->assertRedirect(route('login'));
});

test('authenticated users can view another users profile', function () {
    $viewer = User::factory()->create(['name' => 'Viewer']);
    $profileUser = User::factory()->create([
        'name' => 'Profile User',
        'total_points' => 350,
    ]);

    $this->actingAs($viewer)
        ->get(route('users.show', $profileUser))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('users/Show')
            ->where('profile.id', $profileUser->id)
            ->where('profile.name', 'Profile User')
            ->where('profile.totalPoints', 350)
            ->where('profile.isCurrentUser', false)
            ->has('finishedGames')
            ->has('earnedAchievements')
            ->where('achievementSummary.earned', 0)
            ->where('achievementSummary.total', 27));
});

test('profile response does not expose email', function () {
    $viewer = User::factory()->create();
    $profileUser = User::factory()->create(['email' => 'secret@example.com']);

    $this->actingAs($viewer)
        ->get(route('users.show', $profileUser))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->missing('profile.email'));
});

test('own profile sets is current user to true', function () {
    $user = User::factory()->create(['name' => 'Me']);

    $this->actingAs($user)
        ->get(route('users.show', $user))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('profile.isCurrentUser', true));
});

test('profile shows rank from leaderboard', function () {
    $currentUser = User::factory()->create(['name' => 'Me', 'total_points' => 200]);
    $profileUser = User::factory()->create(['name' => 'Leader', 'total_points' => 400]);
    User::factory()->create(['name' => 'Other', 'total_points' => 100]);

    $this->actingAs($currentUser)
        ->get(route('users.show', $profileUser))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('profile.rank', 1)
            ->where('profile.totalPoints', 400));
});

test('finished games history includes only final games with predictions', function () {
    $viewer = User::factory()->create();
    $profileUser = User::factory()->create();

    $finishedGame = Game::factory()->finished()->create([
        'home_name' => 'Brazil',
        'away_name' => 'Argentina',
    ]);

    $upcomingGame = Game::factory()->create([
        'scheduled_at' => now()->addDay(),
        'is_final' => false,
    ]);

    $finishedWithoutPrediction = Game::factory()->finished()->create();

    Prediction::factory()->for($profileUser)->for($finishedGame)->create([
        'home_score' => 2,
        'away_score' => 1,
        'points' => 200,
    ]);

    Prediction::factory()->for($profileUser)->for($upcomingGame)->create([
        'home_score' => 1,
        'away_score' => 0,
    ]);

    $this->actingAs($viewer)
        ->get(route('users.show', $profileUser))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('finishedGames.data', 1)
            ->where('finishedGames.data.0.id', $finishedGame->id)
            ->where('finishedGames.data.0.matchTitle', 'Brazil x Argentina')
            ->where('finishedGames.data.0.prediction.homeScore', 2)
            ->where('finishedGames.data.0.prediction.awayScore', 1)
            ->where('finishedGames.data.0.prediction.points', 200)
            ->where('finishedGames.data.0.result.homeScore', 2)
            ->where('finishedGames.data.0.result.awayScore', 1));
});

test('viewing a missing user returns not found', function () {
    $viewer = User::factory()->create();

    $this->actingAs($viewer)
        ->get('/users/99999')
        ->assertNotFound();
});
