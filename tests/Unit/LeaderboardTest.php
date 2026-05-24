<?php

use App\Models\User;
use App\Support\Leaderboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

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
