<?php

use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function fifaFixture(string $fixture): array
{
    return json_decode(
        file_get_contents(base_path("tests/Fixtures/fifa/{$fixture}")),
        true,
    );
}

function fakeFifaCalendarMatches(string $fixture): void
{
    Http::fake([
        '*' => Http::response(fifaFixture($fixture)),
    ]);
}

function fakeFifaCalendarMatchSequence(string ...$fixtures): void
{
    $sequence = Http::sequence();

    foreach ($fixtures as $fixture) {
        $sequence->push(fifaFixture($fixture));
    }

    Http::fake([
        '*' => $sequence,
    ]);
}

test('sync fifa game results command logs sync activity', function () {
    Event::fake([MessageLogged::class]);
    fakeFifaCalendarMatches('calendar-matches.json');

    $this->artisan('games:sync-fifa-results')
        ->assertSuccessful();

    Event::assertDispatched(MessageLogged::class, fn (MessageLogged $event) => $event->level === 'info' && $event->message === 'Starting FIFA game results sync.');
    Event::assertDispatched(MessageLogged::class, fn (MessageLogged $event) => $event->level === 'info' && $event->message === 'Updated 0 game results from FIFA.');
});

test('sync fifa games command logs sync activity', function () {
    Event::fake([MessageLogged::class]);
    fakeFifaCalendarMatches('calendar-matches.json');

    $this->artisan('games:sync-fifa')
        ->assertSuccessful();

    Event::assertDispatched(MessageLogged::class, fn (MessageLogged $event) => $event->level === 'info' && $event->message === 'Starting FIFA games sync.');
    Event::assertDispatched(MessageLogged::class, fn (MessageLogged $event) => $event->level === 'info' && $event->message === 'Synced 2 games from FIFA.');
});

test('sync fifa games command creates games from api', function () {
    fakeFifaCalendarMatches('calendar-matches.json');

    $this->artisan('games:sync-fifa')
        ->assertSuccessful();

    expect(Game::query()->count())->toBe(2);

    $game = Game::query()->where('fifa_match_id', '400021443')->first();

    expect($game)->not->toBeNull()
        ->and($game->home_name)->toBe('Mexico')
        ->and($game->away_name)->toBe('South Africa')
        ->and($game->stage_name)->toBe('First Stage')
        ->and($game->group_name)->toBe('Group A')
        ->and($game->is_final)->toBeFalse();
});

test('sync fifa games command updates changed schedule on second run', function () {
    fakeFifaCalendarMatchSequence('calendar-matches.json', 'calendar-matches-updated.json');

    $this->artisan('games:sync-fifa')->assertSuccessful();

    $originalScheduledAt = Game::query()
        ->where('fifa_match_id', '400021443')
        ->value('scheduled_at');

    $this->artisan('games:sync-fifa')->assertSuccessful();

    $game = Game::query()->where('fifa_match_id', '400021443')->first();

    expect(Game::query()->count())->toBe(2)
        ->and($game->scheduled_at->toIso8601String())->not->toBe($originalScheduledAt->toIso8601String())
        ->and($game->scheduled_at->format('Y-m-d'))->toBe('2026-06-12');
});

test('sync fifa game results command updates scores for kicked off matches', function () {
    fakeFifaCalendarMatchSequence('calendar-matches.json', 'calendar-matches-finished.json');

    $this->artisan('games:sync-fifa')->assertSuccessful();

    $game = Game::query()->where('fifa_match_id', '400021443')->first();
    $game->update([
        'scheduled_at' => now()->subHour(),
        'is_final' => false,
    ]);

    $this->artisan('games:sync-fifa-results')
        ->assertSuccessful();

    $game->refresh();

    expect($game->home_score)->toBe(2)
        ->and($game->away_score)->toBe(1)
        ->and($game->match_status)->toBe(4)
        ->and($game->is_final)->toBeTrue();
});

test('sync fifa game results command marks match final with match status zero and result type one', function () {
    fakeFifaCalendarMatchSequence('calendar-matches.json', 'calendar-matches-finished-status0.json');

    $this->artisan('games:sync-fifa')->assertSuccessful();

    $game = Game::query()->where('fifa_match_id', '400021443')->first();
    $game->update([
        'scheduled_at' => now()->subHour(),
        'is_final' => false,
    ]);

    $user = User::factory()->create(['total_points' => 0]);

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 0,
    ]);

    $this->artisan('games:sync-fifa-results')
        ->assertSuccessful();

    $game->refresh();
    $user->refresh();

    expect($game->home_score)->toBe(2)
        ->and($game->away_score)->toBe(0)
        ->and($game->match_status)->toBe(0)
        ->and($game->is_final)->toBeTrue()
        ->and($game->scored_at)->not->toBeNull()
        ->and($user->predictions()->first()->points)->toBe(200)
        ->and($user->total_points)->toBe(200);
});

test('sync fifa game results command skips games that have not kicked off', function () {
    fakeFifaCalendarMatchSequence('calendar-matches.json', 'calendar-matches-finished.json');

    $this->artisan('games:sync-fifa')->assertSuccessful();

    Game::query()->update(['scheduled_at' => now()->addDay()]);

    $this->artisan('games:sync-fifa-results')
        ->assertSuccessful()
        ->expectsOutputToContain('Updated 0 game results');

    $game = Game::query()->where('fifa_match_id', '400021443')->first();

    expect($game->home_score)->toBeNull()
        ->and($game->away_score)->toBeNull()
        ->and($game->is_final)->toBeFalse();
});

test('sync fifa game results command rescores predictions when a final match is corrected', function () {
    fakeFifaCalendarMatchSequence(
        'calendar-matches.json',
        'calendar-matches-finished.json',
        'calendar-matches-corrected.json',
    );

    $this->artisan('games:sync-fifa')->assertSuccessful();

    $game = Game::query()->where('fifa_match_id', '400021443')->first();
    $game->update([
        'scheduled_at' => now()->subHour(),
        'is_final' => false,
    ]);

    $this->artisan('games:sync-fifa-results')->assertSuccessful();

    $game->refresh();

    expect($game->home_score)->toBe(2)
        ->and($game->away_score)->toBe(1)
        ->and($game->is_final)->toBeTrue();

    $user = User::factory()->create(['total_points' => 200]);

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
        'points' => 200,
        'scored_at' => now(),
    ]);

    $this->artisan('games:sync-fifa-results')->assertSuccessful();

    $game->refresh();
    $user->refresh();

    expect($game->home_score)->toBe(3)
        ->and($user->predictions()->first()->points)->toBe(95)
        ->and($user->total_points)->toBe(95);
});

test('sync fifa game results command keeps live matches from being marked final', function () {
    fakeFifaCalendarMatchSequence('calendar-matches.json', 'calendar-matches-live.json');

    $this->artisan('games:sync-fifa')->assertSuccessful();

    $game = Game::query()->where('fifa_match_id', '400021443')->first();
    $game->update([
        'scheduled_at' => now()->subHour(),
        'is_final' => false,
    ]);

    $this->artisan('games:sync-fifa-results')->assertSuccessful();

    $game->refresh();

    expect($game->home_score)->toBe(1)
        ->and($game->away_score)->toBe(0)
        ->and($game->match_status)->toBe(3)
        ->and($game->is_final)->toBeFalse()
        ->and($game->scored_at)->toBeNull();
});

test('sync fifa game results command rolls back points when a live match was wrongly marked final', function () {
    fakeFifaCalendarMatchSequence('calendar-matches.json', 'calendar-matches-live.json');

    $this->artisan('games:sync-fifa')->assertSuccessful();

    $game = Game::query()->where('fifa_match_id', '400021443')->first();
    $game->update([
        'scheduled_at' => now()->subHour(),
        'is_final' => true,
        'home_score' => 1,
        'away_score' => 0,
        'match_status' => 3,
        'scored_at' => now(),
    ]);

    $user = User::factory()->create(['total_points' => 75]);

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
        'points' => 75,
        'scored_at' => now(),
    ]);

    $this->artisan('games:sync-fifa-results')->assertSuccessful();

    $game->refresh();
    $user->refresh();

    expect($game->is_final)->toBeFalse()
        ->and($game->scored_at)->toBeNull()
        ->and($user->predictions()->first()->points)->toBeNull()
        ->and($user->predictions()->first()->scored_at)->toBeNull()
        ->and($user->total_points)->toBe(0);
});
