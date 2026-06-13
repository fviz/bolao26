<?php

use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('profile page includes earned achievements', function () {
    $user = User::factory()->create();
    $achievement = Achievement::query()->where('slug', 'primeiro-chute')->firstOrFail();

    UserAchievement::query()->create([
        'user_id' => $user->id,
        'achievement_id' => $achievement->id,
        'awarded_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('users.show', $user))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('users/Show')
            ->has('earnedAchievements', 1)
            ->where('earnedAchievements.0.slug', 'primeiro-chute')
            ->where('earnedAchievements.0.tierLabel', 'Cobre')
            ->where('achievementSummary.earned', 1)
            ->where('achievementSummary.total', 26));
});

test('profile page limits earned achievements preview to six most recent', function () {
    $user = User::factory()->create();
    $achievements = Achievement::query()->orderBy('sort_order')->take(7)->get();

    foreach ($achievements as $index => $achievement) {
        UserAchievement::query()->create([
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
            'awarded_at' => now()->subDays(7 - $index),
        ]);
    }

    $this->actingAs($user)
        ->get(route('users.show', $user))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('earnedAchievements', 6)
            ->where('earnedAchievements.0.slug', $achievements[6]->slug)
            ->where('achievementSummary.earned', 7)
            ->where('achievementSummary.total', 26));
});

test('user can view all achievements page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.achievements.index', $user))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('achievements/Index')
            ->has('achievements', 26)
            ->where('profile.id', $user->id)
            ->where('achievementSummary.earned', 0)
            ->where('achievementSummary.total', 26)
            ->where('sort', 'catalog')
            ->where('achievements.0.tierLabel', 'Cobre'));
});

test('achievements page can sort by name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.achievements.index', ['user' => $user, 'sort' => 'name']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('sort', 'name')
            ->where('achievements.0.name', 'Atrasado do Enem'));
});

test('achievements page can sort by tier ascending', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.achievements.index', ['user' => $user, 'sort' => 'tier_asc']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('sort', 'tier_asc')
            ->where('achievements.0.slug', 'primeiro-chute')
            ->where('achievements.0.tier', 'bronze')
            ->where('achievements.24.slug', 'frieza-total')
            ->where('achievements.25.slug', 'dono-da-copa')
            ->where('achievements.23.tier', 'diamond'));
});

test('achievements page can sort by tier descending', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.achievements.index', ['user' => $user, 'sort' => 'tier_desc']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('sort', 'tier_desc')
            ->where('achievements.0.slug', 'iluminado')
            ->where('achievements.0.tier', 'diamond')
            ->where('achievements.24.slug', 'leigo-da-bola')
            ->where('achievements.25.slug', 'atrasado-do-enem')
            ->where('achievements.23.tier', 'bronze'));
});

test('achievements page can sort by last awarded', function () {
    $user = User::factory()->create();
    $first = Achievement::query()->where('slug', 'primeiro-chute')->firstOrFail();
    $second = Achievement::query()->where('slug', 'na-gaveta')->firstOrFail();

    UserAchievement::query()->create([
        'user_id' => $user->id,
        'achievement_id' => $first->id,
        'awarded_at' => now()->subDay(),
    ]);

    UserAchievement::query()->create([
        'user_id' => $user->id,
        'achievement_id' => $second->id,
        'awarded_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('users.achievements.index', ['user' => $user, 'sort' => 'awarded']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('sort', 'awarded')
            ->where('achievements.0.slug', 'na-gaveta')
            ->where('achievements.1.slug', 'primeiro-chute'));
});

test('user can view achievement detail page', function () {
    $user = User::factory()->create();
    $achievement = Achievement::query()->where('slug', 'na-gaveta')->firstOrFail();

    $this->actingAs($user)
        ->get(route('users.achievements.show', [$user, $achievement]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('achievements/Show')
            ->where('achievement.slug', 'na-gaveta')
            ->where('achievement.name', 'Na Gaveta')
            ->where('profile.id', $user->id)
            ->where('achievementEarnedPercentage', 0));
});

test('achievement detail page includes earned percentage', function () {
    $achievement = Achievement::query()->where('slug', 'primeiro-chute')->firstOrFail();
    $earnedUsers = User::factory()->count(2)->create();
    $otherUsers = User::factory()->count(2)->create();

    foreach ($earnedUsers as $earnedUser) {
        UserAchievement::query()->create([
            'user_id' => $earnedUser->id,
            'achievement_id' => $achievement->id,
            'awarded_at' => now(),
        ]);
    }

    $this->actingAs($otherUsers->first())
        ->get(route('users.achievements.show', [$otherUsers->first(), $achievement]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('achievementEarnedPercentage', 50));
});

test('guests cannot view achievements pages', function () {
    $user = User::factory()->create();
    $achievement = Achievement::query()->firstOrFail();

    $this->get(route('users.achievements.index', $user))
        ->assertRedirect(route('login'));

    $this->get(route('users.achievements.show', [$user, $achievement]))
        ->assertRedirect(route('login'));
});
