<?php

use App\Models\Achievement;
use App\Models\ChampionPrediction;
use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use App\Models\UserAchievement;
use App\Support\Achievements\AchievementAwardContextResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('achievement show page includes game award context', function () {
    $user = User::factory()->create();
    $achievement = Achievement::query()->where('slug', 'na-gaveta')->firstOrFail();
    $game = Game::factory()->create([
        'home_name' => 'Brazil',
        'home_abbr' => 'BRA',
        'away_name' => 'Argentina',
        'away_abbr' => 'ARG',
    ]);

    UserAchievement::query()->create([
        'user_id' => $user->id,
        'achievement_id' => $achievement->id,
        'awarded_at' => now(),
        'context' => ['game_id' => $game->id],
    ]);

    $this->actingAs($user)
        ->get(route('users.achievements.show', [$user, $achievement]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('achievements/Show')
            ->where('achievement.awardContext.type', 'game')
            ->where('achievement.awardContext.trigger', 'after_match')
            ->where('achievement.awardContext.game.id', $game->id)
            ->where('achievement.awardContext.game.home.displayName', 'Brazil')
            ->where('achievement.awardContext.game.away.displayName', 'Argentina'));
});

test('achievement show page includes match day award context', function () {
    $user = User::factory()->create();
    $achievement = Achievement::query()->where('slug', 'dupla-de-honra')->firstOrFail();
    $matchDay = '2026-06-15';

    $games = Game::factory()->count(2)->finished()->create([
        'local_scheduled_at' => $matchDay.' 18:00:00',
        'scheduled_at' => $matchDay.' 21:00:00',
        'home_name' => 'Brazil',
        'away_name' => 'France',
    ]);

    UserAchievement::query()->create([
        'user_id' => $user->id,
        'achievement_id' => $achievement->id,
        'awarded_at' => now(),
        'context' => [
            'match_day' => $matchDay,
            'game_id' => $games->last()->id,
        ],
    ]);

    $this->actingAs($user)
        ->get(route('users.achievements.show', [$user, $achievement]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('achievement.awardContext.type', 'match_day')
            ->where('achievement.awardContext.matchDay', $matchDay)
            ->has('achievement.awardContext.games', 2));
});

test('achievement show page includes champion award context', function () {
    $user = User::factory()->create();
    $achievement = Achievement::query()->where('slug', 'o-profeta')->firstOrFail();

    UserAchievement::query()->create([
        'user_id' => $user->id,
        'achievement_id' => $achievement->id,
        'awarded_at' => now(),
        'context' => [
            'fifa_team_id' => 'team-bra',
            'team_name' => 'Brazil',
            'team_abbr' => 'BRA',
        ],
    ]);

    $this->actingAs($user)
        ->get(route('users.achievements.show', [$user, $achievement]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('achievement.awardContext.type', 'champion')
            ->where('achievement.awardContext.team.name', 'Brazil'));
});

test('achievement award context resolver resolves game context', function () {
    $user = User::factory()->create();
    $achievement = Achievement::query()->where('slug', 'primeiro-chute')->firstOrFail();
    $game = Game::factory()->create([
        'home_name' => 'Spain',
        'away_name' => 'Germany',
    ]);

    $userAchievement = UserAchievement::query()->create([
        'user_id' => $user->id,
        'achievement_id' => $achievement->id,
        'awarded_at' => now(),
        'context' => ['game_id' => $game->id],
    ]);

    $resolved = AchievementAwardContextResolver::resolve($userAchievement->fresh(), $achievement);

    expect($resolved)
        ->toMatchArray([
            'type' => 'game',
            'trigger' => 'on_prediction',
        ])
        ->and($resolved['game']['id'])->toBe($game->id)
        ->and($resolved['game']['home']['displayName'])->toBe('Spain');
});

test('achievement show page lazily enriches legacy medals with null context', function () {
    $user = User::factory()->create();
    $achievement = Achievement::query()->where('slug', 'na-gaveta')->firstOrFail();
    $game = Game::factory()->finished([
        'home_name' => 'Brazil',
        'away_name' => 'Argentina',
    ])->create([
        'scored_at' => now(),
    ]);

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
        'points' => 200,
    ]);

    UserAchievement::query()->create([
        'user_id' => $user->id,
        'achievement_id' => $achievement->id,
        'awarded_at' => $game->scored_at,
        'context' => null,
    ]);

    $this->actingAs($user)
        ->get(route('users.achievements.show', [$user, $achievement]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('achievement.awardContext.type', 'game')
            ->where('achievement.awardContext.game.id', $game->id)
            ->where('achievement.awardContext.game.home.displayName', 'Brazil'));

    expect(userAchievementContext($user->fresh(), 'na-gaveta'))
        ->toMatchArray(['game_id' => $game->id]);
});

test('enrich context command backfills missing game context', function () {
    $user = User::factory()->create();
    $achievement = Achievement::query()->where('slug', 'na-gaveta')->firstOrFail();
    $game = Game::factory()->finished([
        'home_name' => 'Brazil',
        'away_name' => 'Argentina',
    ])->create([
        'scored_at' => now(),
    ]);

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
        'points' => 200,
    ]);

    UserAchievement::query()->create([
        'user_id' => $user->id,
        'achievement_id' => $achievement->id,
        'awarded_at' => $game->scored_at,
        'context' => null,
    ]);

    $this->artisan('achievements:enrich-context', ['--user' => $user->id])
        ->assertSuccessful();

    expect(userAchievementContext($user->fresh(), 'na-gaveta'))
        ->toMatchArray(['game_id' => $game->id]);
});

test('enrich context command backfills champion context', function () {
    $user = User::factory()->create();
    $achievement = Achievement::query()->where('slug', 'o-profeta')->firstOrFail();

    Game::factory()->create([
        'home_fifa_team_id' => 'team-bra',
        'home_name' => 'Brazil',
        'home_abbr' => 'BRA',
    ]);

    ChampionPrediction::factory()->create([
        'user_id' => $user->id,
        'fifa_team_id' => 'team-bra',
        'points' => 300,
    ]);

    UserAchievement::query()->create([
        'user_id' => $user->id,
        'achievement_id' => $achievement->id,
        'awarded_at' => now(),
        'context' => null,
    ]);

    $this->artisan('achievements:enrich-context', ['--user' => $user->id])
        ->assertSuccessful();

    expect(userAchievementContext($user->fresh(), 'o-profeta'))
        ->toMatchArray([
            'fifa_team_id' => 'team-bra',
            'team_name' => 'Brazil',
            'team_abbr' => 'BRA',
        ]);
});
