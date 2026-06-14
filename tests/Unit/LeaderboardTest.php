<?php

use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use App\Support\Leaderboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

/**
 * @param  array{diamond?: int, gold?: int, silver?: int, bronze?: int}  $counts
 */
function awardMedals(User $user, array $counts): void
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

test('assigns competition ranks with ties sharing rank', function () {
    User::factory()->create(['name' => 'User A', 'total_points' => 200]);
    User::factory()->create(['name' => 'User B', 'total_points' => 200]);
    User::factory()->create(['name' => 'User C', 'total_points' => 100]);

    $entries = Leaderboard::rankedEntries();

    expect($entries)->toHaveCount(3)
        ->and($entries[0]['rank'])->toBe(1)
        ->and($entries[1]['rank'])->toBe(1)
        ->and($entries[2]['rank'])->toBe(3);
});

test('window centers on the current user when possible', function () {
    foreach (range(1, 12) as $position) {
        User::factory()->create([
            'name' => "User {$position}",
            'total_points' => 1200 - ($position * 10),
        ]);
    }

    $tenthUser = User::query()->where('name', 'User 10')->firstOrFail();
    $entries = Leaderboard::rankedEntries();
    $window = Leaderboard::windowForUser($entries, $tenthUser->id, 5);

    expect($window)->toHaveCount(5)
        ->and($window->pluck('rank')->all())->toBe([8, 9, 10, 11, 12])
        ->and($window->firstWhere('id', $tenthUser->id)['rank'])->toBe(10);
});

test('window returns all entries when fewer than window size', function () {
    User::factory()->count(3)->sequence(
        ['total_points' => 300],
        ['total_points' => 200],
        ['total_points' => 100],
    )->create();

    $middleUser = Leaderboard::rankedEntries()->values()->get(1);
    $window = Leaderboard::windowForUser(
        Leaderboard::rankedEntries(),
        $middleUser['id'],
        5,
    );

    expect($window)->toHaveCount(3);
});

test('window shows first positions when user is near the top', function () {
    foreach (range(1, 8) as $position) {
        User::factory()->create([
            'name' => "User {$position}",
            'total_points' => 800 - ($position * 10),
        ]);
    }

    $secondUser = User::query()->where('name', 'User 2')->firstOrFail();
    $window = Leaderboard::windowForUser(
        Leaderboard::rankedEntries(),
        $secondUser->id,
        5,
    );

    expect($window->pluck('rank')->all())->toBe([1, 2, 3, 4, 5]);
});

test('window shows last positions when user is near the bottom', function () {
    foreach (range(1, 8) as $position) {
        User::factory()->create([
            'name' => "User {$position}",
            'total_points' => 800 - ($position * 10),
        ]);
    }

    $lastUser = User::query()->where('name', 'User 8')->firstOrFail();
    $window = Leaderboard::windowForUser(
        Leaderboard::rankedEntries(),
        $lastUser->id,
        5,
    );

    expect($window->pluck('rank')->all())->toBe([4, 5, 6, 7, 8]);
});

test('marks the current user in ranked entries', function () {
    $user = User::factory()->create(['total_points' => 50]);
    User::factory()->create(['total_points' => 100]);

    $entries = Leaderboard::rankedEntries($user);

    expect($entries->firstWhere('id', $user->id)['isCurrentUser'])->toBeTrue()
        ->and($entries->where('isCurrentUser', true))->toHaveCount(1);
});

test('ranked entries include user avatar url', function () {
    $user = User::factory()->create([
        'avatar_path' => 'avatars/example.jpg',
        'total_points' => 50,
    ]);

    $entries = Leaderboard::rankedEntries();

    expect($entries->firstWhere('id', $user->id)['avatar'])->toBe($user->avatar);
});

test('medal ranking prefers gold over many silvers', function () {
    $goldAndSilver = User::factory()->create(['name' => 'Gold Leader']);
    $manySilvers = User::factory()->create(['name' => 'Silver Leader']);

    awardMedals($goldAndSilver, ['gold' => 1, 'silver' => 2]);
    awardMedals($manySilvers, ['silver' => 6]);

    $entries = Leaderboard::medalRankedEntries();

    expect($entries->first()['id'])->toBe($goldAndSilver->id)
        ->and($entries->first()['goldCount'])->toBe(1)
        ->and($entries->first()['silverCount'])->toBe(2)
        ->and($entries->last()['id'])->toBe($manySilvers->id)
        ->and($entries->last()['goldCount'])->toBe(0)
        ->and($entries->last()['silverCount'])->toBe(6);
});

test('medal ranking prefers diamond over many golds', function () {
    $diamondUser = User::factory()->create(['name' => 'Diamond Leader']);
    $goldUser = User::factory()->create(['name' => 'Gold Leader']);

    awardMedals($diamondUser, ['diamond' => 1]);
    awardMedals($goldUser, ['gold' => 5]);

    $entries = Leaderboard::medalRankedEntries();

    expect($entries->first()['id'])->toBe($diamondUser->id)
        ->and($entries->first()['diamondCount'])->toBe(1)
        ->and($entries->last()['id'])->toBe($goldUser->id)
        ->and($entries->last()['goldCount'])->toBe(5);
});

test('medal ranking assigns competition ranks with ties sharing rank', function () {
    $userA = User::factory()->create(['name' => 'User A']);
    $userB = User::factory()->create(['name' => 'User B']);
    $userC = User::factory()->create(['name' => 'User C']);

    awardMedals($userA, ['gold' => 1, 'silver' => 1]);
    awardMedals($userB, ['gold' => 1, 'silver' => 1]);
    awardMedals($userC, ['bronze' => 1]);

    $entries = Leaderboard::medalRankedEntries();

    expect($entries)->toHaveCount(3)
        ->and($entries[0]['rank'])->toBe(1)
        ->and($entries[1]['rank'])->toBe(1)
        ->and($entries[2]['rank'])->toBe(3);
});

test('medal ranked entries mark the current user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    awardMedals($user, ['bronze' => 1]);
    awardMedals($other, ['gold' => 1]);

    $entries = Leaderboard::medalRankedEntries($user);

    expect($entries->firstWhere('id', $user->id)['isCurrentUser'])->toBeTrue()
        ->and($entries->where('isCurrentUser', true))->toHaveCount(1);
});

test('medal ranked entries include user avatar url', function () {
    $user = User::factory()->create([
        'avatar_path' => 'avatars/example.jpg',
    ]);

    awardMedals($user, ['bronze' => 1]);

    $entries = Leaderboard::medalRankedEntries();

    expect($entries->firstWhere('id', $user->id)['avatar'])->toBe($user->avatar);
});

test('medal ranked entries include users with zero medals', function () {
    $user = User::factory()->create();

    $entries = Leaderboard::medalRankedEntries();

    expect($entries)->toHaveCount(1)
        ->and($entries->first()['diamondCount'])->toBe(0)
        ->and($entries->first()['goldCount'])->toBe(0)
        ->and($entries->first()['silverCount'])->toBe(0)
        ->and($entries->first()['bronzeCount'])->toBe(0)
        ->and($entries->first()['lixoHumanoCount'])->toBe(0)
        ->and($entries->first()['rank'])->toBe(1);
});

test('medal ranked entries include lixo humano count', function () {
    $user = User::factory()->create();
    $achievement = Achievement::query()->where('slug', 'traidor-da-patria')->firstOrFail();

    UserAchievement::query()->create([
        'user_id' => $user->id,
        'achievement_id' => $achievement->id,
        'awarded_at' => now(),
    ]);

    $entries = Leaderboard::medalRankedEntries();

    expect($entries->firstWhere('id', $user->id)['lixoHumanoCount'])->toBe(1);
});
