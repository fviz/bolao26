<?php

use App\Models\Achievement;
use App\Models\Game;
use App\Models\GameComment;
use App\Models\User;
use App\Models\UserAchievement;
use App\Support\Leaderboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function awardAchievement(User $user, string $slug): Achievement
{
    $achievement = Achievement::query()->where('slug', $slug)->firstOrFail();

    UserAchievement::query()->create([
        'user_id' => $user->id,
        'achievement_id' => $achievement->id,
        'awarded_at' => now(),
    ]);

    return $achievement;
}

test('user can set featured medal they earned', function () {
    $user = User::factory()->create();
    $achievement = awardAchievement($user, 'primeiro-chute');

    $this->actingAs($user)
        ->patch(route('users.featured-achievement.update', $user), [
            'achievementSlug' => $achievement->slug,
        ])
        ->assertRedirect();

    expect($user->fresh()->featured_achievement_id)->toBe($achievement->id);
});

test('user cannot set unearned medal as featured', function () {
    $user = User::factory()->create();
    $achievement = Achievement::query()->where('slug', 'primeiro-chute')->firstOrFail();

    $this->actingAs($user)
        ->patch(route('users.featured-achievement.update', $user), [
            'achievementSlug' => $achievement->slug,
        ])
        ->assertSessionHasErrors('achievementSlug');

    expect($user->fresh()->featured_achievement_id)->toBeNull();
});

test('user cannot update another users featured medal', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $achievement = awardAchievement($user, 'primeiro-chute');

    $this->actingAs($otherUser)
        ->patch(route('users.featured-achievement.update', $user), [
            'achievementSlug' => $achievement->slug,
        ])
        ->assertForbidden();

    expect($user->fresh()->featured_achievement_id)->toBeNull();
});

test('user can clear featured medal', function () {
    $user = User::factory()->create();
    $achievement = awardAchievement($user, 'primeiro-chute');

    $user->featured_achievement_id = $achievement->id;
    $user->save();

    $this->actingAs($user)
        ->patch(route('users.featured-achievement.update', $user), [
            'achievementSlug' => '',
        ])
        ->assertRedirect();

    expect($user->fresh()->featured_achievement_id)->toBeNull();
});

test('profile page includes featured achievement', function () {
    $user = User::factory()->create();
    $achievement = awardAchievement($user, 'primeiro-chute');
    $user->featured_achievement_id = $achievement->id;
    $user->save();

    $this->actingAs($user)
        ->get(route('users.show', $user))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('profile.featuredAchievement.slug', 'primeiro-chute')
            ->where('profile.featuredAchievement.emoji', $achievement->emoji)
            ->where('earnedAchievements.0.isFeatured', true));
});

test('leaderboard entries include featured achievement', function () {
    $user = User::factory()->create(['total_points' => 100]);
    $achievement = awardAchievement($user, 'primeiro-chute');
    $user->featured_achievement_id = $achievement->id;
    $user->save();

    $entries = Leaderboard::rankedEntries($user);
    $entry = $entries->firstWhere('id', $user->id);

    expect($entry)->not->toBeNull()
        ->and($entry['featuredAchievement']['slug'])->toBe('primeiro-chute');
});

test('game comments include featured achievement', function () {
    $viewer = User::factory()->create();
    $author = User::factory()->create(['name' => 'Comment Author']);
    $achievement = awardAchievement($author, 'primeiro-chute');
    $author->featured_achievement_id = $achievement->id;
    $author->save();

    $game = Game::factory()->create();
    GameComment::factory()->create([
        'game_id' => $game->id,
        'user_id' => $author->id,
        'body' => 'Great game!',
    ]);

    $this->actingAs($viewer)
        ->get(route('games.show', $game))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('comments.0.userName', 'Comment Author')
            ->where('comments.0.featuredAchievement.slug', 'primeiro-chute'));
});

test('achievement show page marks featured medal', function () {
    $user = User::factory()->create();
    $achievement = awardAchievement($user, 'primeiro-chute');
    $user->featured_achievement_id = $achievement->id;
    $user->save();

    $this->actingAs($user)
        ->get(route('users.achievements.show', [$user, $achievement]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('achievement.isFeatured', true)
            ->where('achievement.slug', 'primeiro-chute'));
});

test('user with traidor da patria cannot change featured medal', function () {
    $user = User::factory()->create();
    $traidor = awardAchievement($user, 'traidor-da-patria');
    $user->featured_achievement_id = $traidor->id;
    $user->save();

    $other = Achievement::query()->where('slug', 'primeiro-chute')->firstOrFail();
    awardAchievement($user, 'primeiro-chute');

    $this->actingAs($user)
        ->patch(route('users.featured-achievement.update', $user), [
            'achievementSlug' => $other->slug,
        ])
        ->assertSessionHasErrors('achievementSlug');

    expect($user->fresh()->featured_achievement_id)->toBe($traidor->id);
});

test('user with traidor da patria cannot clear featured medal', function () {
    $user = User::factory()->create();
    $traidor = awardAchievement($user, 'traidor-da-patria');
    $user->featured_achievement_id = $traidor->id;
    $user->save();

    $this->actingAs($user)
        ->patch(route('users.featured-achievement.update', $user), [
            'achievementSlug' => '',
        ])
        ->assertSessionHasErrors('achievementSlug');

    expect($user->fresh()->featured_achievement_id)->toBe($traidor->id);
});

test('profile page marks featured achievement as locked for traidor da patria', function () {
    $user = User::factory()->create();
    $traidor = awardAchievement($user, 'traidor-da-patria');
    $user->featured_achievement_id = $traidor->id;
    $user->save();

    $this->actingAs($user)
        ->get(route('users.show', $user))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('profile.featuredAchievementLocked', true)
            ->where('profile.featuredAchievement.slug', 'traidor-da-patria'));
});
