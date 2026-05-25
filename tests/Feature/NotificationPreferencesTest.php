<?php

use App\Models\User;
use App\Notifications\TestBrowserNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use NotificationChannels\WebPush\WebPushChannel;

test('notification settings page is displayed with default preferences', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('notifications.settings.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Notifications')
            ->where('preferences.missingPredictionRemindersEnabled', true)
            ->where('preferences.gameResultNotificationsEnabled', true)
            ->where('preferences.dailySummaryEnabled', true)
            ->where('preferences.tournamentDeadlineEnabled', true)
            ->where('preferences.browserNotificationsEnabled', false)
            ->where('preferences.gameReminderMinutes', 60)
            ->where('preferences.dailySummaryTime', '09:00'),
        );
});

test('notification settings can be updated', function () {
    $user = User::factory()->create();
    $user->notificationPreference()->create([
        'missing_prediction_reminders_enabled' => true,
        'game_result_notifications_enabled' => false,
        'daily_summary_enabled' => false,
        'tournament_deadline_enabled' => true,
        'game_reminder_minutes' => 60,
        'daily_summary_time' => '09:00',
        'daily_summary_timezone' => 'UTC',
    ]);

    $this->actingAs($user)
        ->patch(route('notifications.settings.update'), [
            'missing_prediction_reminders_enabled' => false,
            'game_result_notifications_enabled' => true,
            'daily_summary_enabled' => true,
            'tournament_deadline_enabled' => false,
            'browser_notifications_enabled' => false,
            'game_reminder_minutes' => 180,
            'daily_summary_time' => '08:30',
            'daily_summary_timezone' => 'America/Sao_Paulo',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('notifications.settings.edit'));

    $preference = $user->notificationPreference()->first();

    expect($preference)->not->toBeNull()
        ->and($preference->missing_prediction_reminders_enabled)->toBeFalse()
        ->and($preference->game_result_notifications_enabled)->toBeTrue()
        ->and($preference->daily_summary_enabled)->toBeTrue()
        ->and($preference->tournament_deadline_enabled)->toBeFalse()
        ->and($preference->game_reminder_minutes)->toBe(180)
        ->and($preference->daily_summary_time)->toBe('08:30')
        ->and($preference->daily_summary_timezone)->toBe('America/Sao_Paulo')
        ->and($user->notificationPreference()->count())->toBe(1);

    $this->actingAs($user)
        ->get(route('notifications.settings.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('preferences.missingPredictionRemindersEnabled', false)
            ->where('preferences.gameResultNotificationsEnabled', true)
            ->where('preferences.dailySummaryEnabled', true)
            ->where('preferences.tournamentDeadlineEnabled', false)
            ->where('preferences.gameReminderMinutes', 180)
            ->where('preferences.dailySummaryTime', '08:30')
            ->where('preferences.dailySummaryTimezone', 'America/Sao_Paulo'),
        );
});

test('notification settings validate reminder interval and timezone', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('notifications.settings.update'), [
            'missing_prediction_reminders_enabled' => true,
            'game_result_notifications_enabled' => true,
            'daily_summary_enabled' => true,
            'tournament_deadline_enabled' => true,
            'browser_notifications_enabled' => false,
            'game_reminder_minutes' => 45,
            'daily_summary_time' => '25:00',
            'daily_summary_timezone' => 'Mars/Base',
        ])
        ->assertSessionHasErrors([
            'game_reminder_minutes',
            'daily_summary_time',
            'daily_summary_timezone',
        ]);
});

test('push subscription endpoints store and remove browser subscriptions', function () {
    $user = User::factory()->create();
    $payload = [
        'endpoint' => 'https://example.test/push/subscription',
        'keys' => [
            'p256dh' => str_repeat('a', 88),
            'auth' => str_repeat('b', 24),
        ],
        'contentEncoding' => 'aes128gcm',
    ];

    $this->actingAs($user)
        ->postJson(route('push-subscriptions.store'), $payload)
        ->assertOk()
        ->assertJson(['subscribed' => true]);

    expect($user->pushSubscriptions()->where('endpoint', $payload['endpoint'])->exists())->toBeTrue()
        ->and($user->notificationPreference()->first()?->browser_notifications_enabled)->toBeTrue();

    $this->actingAs($user)
        ->deleteJson(route('push-subscriptions.destroy'), ['endpoint' => $payload['endpoint']])
        ->assertOk()
        ->assertJson(['subscribed' => false]);

    expect($user->pushSubscriptions()->where('endpoint', $payload['endpoint'])->exists())->toBeFalse()
        ->and($user->notificationPreference()->first()?->refresh()->browser_notifications_enabled)->toBeFalse();
});

test('current browser push subscription can receive a test notification', function () {
    Notification::fake();
    config([
        'webpush.vapid.public_key' => 'public-key',
        'webpush.vapid.private_key' => 'private-key',
    ]);

    $user = User::factory()->create();
    $payload = [
        'endpoint' => 'https://example.test/push/current-browser',
        'keys' => [
            'p256dh' => str_repeat('a', 88),
            'auth' => str_repeat('b', 24),
        ],
        'contentEncoding' => 'aes128gcm',
    ];

    $this->actingAs($user)
        ->postJson(route('push-subscriptions.store'), $payload)
        ->assertOk();

    $this->actingAs($user)
        ->postJson(route('push-subscriptions.test'), [
            'endpoint' => $payload['endpoint'],
        ])
        ->assertOk()
        ->assertJson(['sent' => true]);

    Notification::assertSentOnDemand(
        TestBrowserNotification::class,
        function (TestBrowserNotification $notification, array $channels, $notifiable) use ($payload): bool {
            $subscriptions = $notifiable->routeNotificationFor('WebPush');

            return $channels === [WebPushChannel::class]
                && $subscriptions instanceof Collection
                && $subscriptions->first()->endpoint === $payload['endpoint'];
        },
    );
});

test('web push channel dependencies are registered', function () {
    expect(app(WebPushChannel::class))->toBeInstanceOf(WebPushChannel::class);
});
