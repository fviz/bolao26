<?php

use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use App\Notifications\DailyMissingPredictionsSummary;
use App\Notifications\GameFinishedPredictionScored;
use App\Notifications\MissingPredictionReminder;
use App\Notifications\TournamentDeadlineReminder;
use App\Services\Scoring\ScoreGamePredictions;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

test('game reminder command logs notification activity', function () {
    Event::fake([MessageLogged::class]);
    Carbon::setTestNow('2026-06-10 12:00:00');
    Notification::fake();

    $user = User::factory()->create();
    $user->notificationPreference()->create([
        'game_reminder_minutes' => 60,
    ]);

    Game::factory()->create([
        'scheduled_at' => now()->addHour()->addMinute(),
    ]);

    $this->artisan('notifications:send-game-reminders')
        ->assertSuccessful();

    Event::assertDispatched(MessageLogged::class, fn (MessageLogged $event) => $event->level === 'info' && $event->message === 'Starting game reminder notifications.');
    Event::assertDispatched(MessageLogged::class, fn (MessageLogged $event) => $event->level === 'info' && $event->message === 'Sent 1 game reminder notifications.');

    Carbon::setTestNow();
});

test('game reminder command notifies users missing a prediction once', function () {
    Carbon::setTestNow('2026-06-10 12:00:00');
    Notification::fake();

    $user = User::factory()->create();
    $user->notificationPreference()->create([
        'game_reminder_minutes' => 60,
    ]);

    Game::factory()->create([
        'scheduled_at' => now()->addHour()->addMinute(),
    ]);

    $this->artisan('notifications:send-game-reminders')
        ->assertSuccessful();

    $this->artisan('notifications:send-game-reminders')
        ->assertSuccessful();

    Notification::assertSentTo($user, MissingPredictionReminder::class);
    Notification::assertCount(1);

    Carbon::setTestNow();
});

test('game reminder command skips disabled reminders', function () {
    Carbon::setTestNow('2026-06-10 12:00:00');
    Notification::fake();

    $user = User::factory()->create();
    $user->notificationPreference()->create([
        'missing_prediction_reminders_enabled' => false,
        'game_reminder_minutes' => 60,
    ]);

    Game::factory()->create([
        'scheduled_at' => now()->addHour()->addMinute(),
    ]);

    $this->artisan('notifications:send-game-reminders')
        ->assertSuccessful();

    Notification::assertNothingSent();

    Carbon::setTestNow();
});

test('daily summary command logs notification activity', function () {
    Event::fake([MessageLogged::class]);
    Carbon::setTestNow('2026-06-10 12:00:00');
    Notification::fake();

    $user = User::factory()->create();
    $user->notificationPreference()->create([
        'daily_summary_time' => '09:00',
        'daily_summary_timezone' => 'America/Sao_Paulo',
    ]);

    Game::factory()->create([
        'scheduled_at' => now()->addHours(2),
    ]);

    $this->artisan('notifications:send-daily-summary')
        ->assertSuccessful();

    Event::assertDispatched(MessageLogged::class, fn (MessageLogged $event) => $event->level === 'info' && $event->message === 'Starting daily summary notifications.');
    Event::assertDispatched(MessageLogged::class, fn (MessageLogged $event) => $event->level === 'info' && $event->message === 'Sent 1 daily summary notifications.');

    Carbon::setTestNow();
});

test('daily summary command uses the user timezone and missing predictions', function () {
    Carbon::setTestNow('2026-06-10 12:00:00');
    Notification::fake();

    $user = User::factory()->create();
    $user->notificationPreference()->create([
        'daily_summary_time' => '09:00',
        'daily_summary_timezone' => 'America/Sao_Paulo',
    ]);

    Game::factory()->create([
        'scheduled_at' => now()->addHours(2),
    ]);

    $this->artisan('notifications:send-daily-summary')
        ->assertSuccessful();

    Notification::assertSentTo($user, DailyMissingPredictionsSummary::class);

    Carbon::setTestNow();
});

test('scoring sends result notifications only to users with predictions', function () {
    Notification::fake();

    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $game = Game::factory()->finished(['home_score' => 2, 'away_score' => 1])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    Notification::assertSentTo($user, GameFinishedPredictionScored::class);
    Notification::assertNotSentTo($otherUser, GameFinishedPredictionScored::class);
});

test('tournament deadline command logs notification activity', function () {
    Event::fake([MessageLogged::class]);
    Carbon::setTestNow('2026-06-10 12:00:00');
    Notification::fake();
    config([
        'bolao.champion_predictions_deadline' => '2026-06-11 00:00:00',
        'bolao.top_scorer_predictions_deadline' => '2026-06-11 00:00:00',
    ]);

    User::factory()->create();

    $this->artisan('notifications:send-tournament-deadline')
        ->assertSuccessful();

    Event::assertDispatched(MessageLogged::class, fn (MessageLogged $event) => $event->level === 'info' && $event->message === 'Starting tournament deadline notifications.');
    Event::assertDispatched(MessageLogged::class, fn (MessageLogged $event) => $event->level === 'info' && $event->message === 'Sent 1 tournament deadline notifications.');

    Carbon::setTestNow();
});

test('tournament deadline command sends one reminder for missing champion or scorer predictions', function () {
    Carbon::setTestNow('2026-06-10 12:00:00');
    Notification::fake();
    config([
        'bolao.champion_predictions_deadline' => '2026-06-11 00:00:00',
        'bolao.top_scorer_predictions_deadline' => '2026-06-11 00:00:00',
    ]);

    $user = User::factory()->create();

    $this->artisan('notifications:send-tournament-deadline')
        ->assertSuccessful();

    $this->artisan('notifications:send-tournament-deadline')
        ->assertSuccessful();

    Notification::assertSentTo($user, TournamentDeadlineReminder::class);
    Notification::assertCount(1);

    Carbon::setTestNow();
});
