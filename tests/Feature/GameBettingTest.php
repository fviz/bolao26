<?php

use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('is betting open is false for past games', function () {
    $game = Game::factory()->create([
        'scheduled_at' => now()->subHour(),
    ]);

    expect($game->isBettingOpen())->toBeFalse();
});

test('is betting open is false when kickoff is less than one minute away', function () {
    $game = Game::factory()->create([
        'scheduled_at' => now()->addSeconds(30),
    ]);

    expect($game->isBettingOpen())->toBeFalse();
});

test('is betting open is true more than one minute before kickoff', function () {
    $game = Game::factory()->create([
        'scheduled_at' => now()->addHours(2),
    ]);

    expect($game->isBettingOpen())->toBeTrue();
});

test('is betting open is false when scheduled at is null', function () {
    $game = Game::factory()->make([
        'scheduled_at' => null,
    ]);

    expect($game->isBettingOpen())->toBeFalse();
});

test('betting open scope matches is betting open for sample games', function () {
    $open = Game::factory()->create([
        'scheduled_at' => now()->addHours(2),
    ]);
    $closed = Game::factory()->create([
        'scheduled_at' => now()->subHour(),
    ]);

    $ids = Game::query()->bettingOpen()->pluck('id');

    expect($ids)->toContain($open->id)
        ->and($ids)->not->toContain($closed->id)
        ->and($open->isBettingOpen())->toBeTrue()
        ->and($closed->isBettingOpen())->toBeFalse();
});

test('past games expose is betting open false in game resource', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create([
        'scheduled_at' => now()->subDay(),
    ]);

    $request = request()->setUserResolver(fn () => $user);
    $payload = GameResource::make($game)->resolve($request);

    expect($payload['isBettingOpen'])->toBeFalse();
});
