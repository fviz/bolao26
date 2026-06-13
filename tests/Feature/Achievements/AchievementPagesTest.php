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
            ->where('earnedAchievements.0.slug', 'primeiro-chute'));
});

test('user can view all achievements page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.achievements.index', $user))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('achievements/Index')
            ->has('achievements', 24)
            ->where('profile.id', $user->id)
            ->where('sort', 'catalog'));
});

test('achievements page can sort by name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.achievements.index', ['user' => $user, 'sort' => 'name']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('sort', 'name')
            ->where('achievements.0.name', 'Contra a Correnteza'));
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
            ->where('profile.id', $user->id));
});

test('guests cannot view achievements pages', function () {
    $user = User::factory()->create();
    $achievement = Achievement::query()->firstOrFail();

    $this->get(route('users.achievements.index', $user))
        ->assertRedirect(route('login'));

    $this->get(route('users.achievements.show', [$user, $achievement]))
        ->assertRedirect(route('login'));
});
