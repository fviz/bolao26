<?php

use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @param  array{diamond?: int, gold?: int, silver?: int, bronze?: int}  $counts
 */
function awardMedalsForRanking(User $user, array $counts): void
{
    $byTier = [
        'diamond' => ['iluminado', 'frieza-total', 'dono-da-copa'],
        'gold' => ['em-chamas', 'dupla-de-honra', 'o-profeta', 'olheiro-de-elite', 'lobo-solitario'],
        'silver' => ['gabaritando-a-agenda', 'hat-trick', 'na-gaveta', 'pe-de-rato', 'coracao-de-aco', 'contra-a-correnteza'],
        'bronze' => ['primeiro-chute', 'saindo-do-zero', 'diplomata', 'mae-dinah', 'no-quase', 'efeito-espelho'],
    ];

    foreach ($counts as $tier => $count) {
        $slugs = array_slice($byTier[$tier], 0, $count);

        foreach ($slugs as $slug) {
            $achievement = Achievement::query()->where('slug', $slug)->firstOrFail();

            UserAchievement::query()->create([
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
                'awarded_at' => now(),
            ]);
        }
    }
}

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
            ->has('leaderboard')
            ->has('medalLeaderboard'));
});

test('ranking page shows medal leaderboard with olympic ordering', function () {
    $currentUser = User::factory()->create(['name' => 'Me']);
    $silverLeader = User::factory()->create(['name' => 'Silver']);
    $goldLeader = User::factory()->create(['name' => 'Gold']);

    awardMedalsForRanking($currentUser, ['gold' => 1, 'silver' => 2]);
    awardMedalsForRanking($silverLeader, ['silver' => 6]);
    awardMedalsForRanking($goldLeader, ['gold' => 1, 'silver' => 2]);

    $this->actingAs($currentUser)
        ->get(route('ranking.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('medalLeaderboard', 3)
            ->where('medalLeaderboard.0.name', 'Gold')
            ->where('medalLeaderboard.1.name', 'Me')
            ->where('medalLeaderboard.0.rank', 1)
            ->where('medalLeaderboard.1.rank', 1)
            ->where('medalLeaderboard.2.rank', 3)
            ->where('medalLeaderboard.1.isCurrentUser', true));
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
