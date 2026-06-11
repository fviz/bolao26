<?php

use App\Models\ChampionPrediction;
use App\Models\Game;
use App\Models\GameComment;
use App\Models\Prediction;
use App\Models\TopScorerPrediction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

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

test('dashboard exposes user prediction on listed games', function () {
    $user = User::factory()->create();

    $predictedGame = Game::factory()->create([
        'scheduled_at' => now()->addDay(),
        'home_name' => 'Brazil',
        'away_name' => 'France',
    ]);

    Prediction::factory()->for($user)->for($predictedGame)->create([
        'home_score' => 2,
        'away_score' => 1,
    ]);

    $unpredictedGame = Game::factory()->create([
        'scheduled_at' => now()->addDays(2),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('games.data.0.id', $predictedGame->id)
            ->where('games.data.0.userPrediction.homeScore', 2)
            ->where('games.data.0.userPrediction.awayScore', 1)
            ->where('games.data.1.id', $unpredictedGame->id)
            ->where('games.data.1.userPrediction', null));
});

test('dashboard lists only upcoming games paginated ten per page', function () {
    $user = User::factory()->create();

    Game::factory()->count(11)->create([
        'scheduled_at' => now()->addDay(),
    ]);

    Game::factory()->past()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('games.data', 10)
            ->where('games.meta.total', 11)
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

test('dashboard exposes open tournament predictions when deadlines are in the future and user has none', function () {
    Config::set('bolao.champion_predictions_deadline', now()->addDay()->toDateTimeString());
    Config::set('bolao.top_scorer_predictions_deadline', now()->addDay()->toDateTimeString());

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('championPredictionsOpen', true)
            ->where('topScorerPredictionsOpen', true)
            ->where('hasChampionPrediction', false)
            ->where('hasTopScorerPrediction', false));
});

test('dashboard exposes tournament prediction presence when user has predictions', function () {
    Config::set('bolao.champion_predictions_deadline', now()->addDay()->toDateTimeString());
    Config::set('bolao.top_scorer_predictions_deadline', now()->addDay()->toDateTimeString());

    $user = User::factory()->create();

    ChampionPrediction::factory()->for($user)->create();
    TopScorerPrediction::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('championPredictionsOpen', true)
            ->where('topScorerPredictionsOpen', true)
            ->where('hasChampionPrediction', true)
            ->where('hasTopScorerPrediction', true));
});

test('dashboard exposes closed tournament predictions when deadlines have passed', function () {
    Config::set('bolao.champion_predictions_deadline', now()->subMinute()->toDateTimeString());
    Config::set('bolao.top_scorer_predictions_deadline', now()->subMinute()->toDateTimeString());

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('championPredictionsOpen', false)
            ->where('topScorerPredictionsOpen', false)
            ->where('hasChampionPrediction', false)
            ->where('hasTopScorerPrediction', false));
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

test('dashboard featured game prioritizes likely live match over last finished', function () {
    $user = User::factory()->create();

    $finishedGame = Game::factory()->finished([
        'home_name' => 'Brazil',
        'away_name' => 'France',
    ])->create([
        'scheduled_at' => now()->subDays(2),
    ]);

    $liveGame = Game::factory()->live()->create([
        'home_name' => 'Argentina',
        'away_name' => 'Germany',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('featuredGame.status', 'live')
            ->where('featuredGame.game.id', $liveGame->id)
            ->where('featuredGame.game.matchTitle', 'Argentina x Germany')
            ->missing('featuredGame.game.result'));
});

test('dashboard featured game shows last finished when no likely live match', function () {
    $user = User::factory()->create();

    Game::factory()->finished([
        'home_score' => 1,
        'away_score' => 0,
    ])->create([
        'scheduled_at' => now()->subDays(3),
        'home_name' => 'Brazil',
        'away_name' => 'France',
    ]);

    $latestFinished = Game::factory()->finished([
        'home_score' => 2,
        'away_score' => 1,
    ])->create([
        'scheduled_at' => now()->subDay(),
        'home_name' => 'Argentina',
        'away_name' => 'Germany',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('featuredGame.status', 'finished')
            ->where('featuredGame.game.id', $latestFinished->id)
            ->where('featuredGame.game.result.homeScore', 2)
            ->where('featuredGame.game.result.awayScore', 1));
});

test('dashboard featured game is null when only upcoming games exist', function () {
    $user = User::factory()->create();

    Game::factory()->create([
        'scheduled_at' => now()->addDay(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('featuredGame', null));
});

test('dashboard featured game excludes stale kickoff from likely live window', function () {
    $user = User::factory()->create();

    Game::factory()->create([
        'scheduled_at' => now()->subHours(5),
        'is_final' => false,
        'home_name' => 'Stale',
        'away_name' => 'Match',
    ]);

    $finishedGame = Game::factory()->finished([
        'home_score' => 3,
        'away_score' => 2,
    ])->create([
        'scheduled_at' => now()->subDay(),
        'home_name' => 'Brazil',
        'away_name' => 'France',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('featuredGame.status', 'finished')
            ->where('featuredGame.game.id', $finishedGame->id)
            ->where('featuredGame.game.result.homeScore', 3)
            ->where('featuredGame.game.result.awayScore', 2));
});
