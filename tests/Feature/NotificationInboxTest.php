<?php

use App\Models\User;
use App\Notifications\DailyMissingPredictionsSummary;
use Inertia\Testing\AssertableInertia as Assert;

test('notifications page lists user notifications', function () {
    $user = User::factory()->create();

    $user->notifyNow(new DailyMissingPredictionsSummary(3, 2), ['database']);

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Notifications')
            ->has('notifications.data', 1)
            ->where('notifications.data.0.title', 'Previsões de hoje')
            ->where('notifications.data.0.readAt', null),
        );
});

test('notifications page exposes browser push availability when vapid keys are configured', function () {
    config([
        'webpush.vapid.public_key' => 'public-key',
        'webpush.vapid.private_key' => 'private-key',
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('browserPushAvailable', true));
});

test('notifications page exposes browser push as unavailable when vapid keys are missing', function () {
    config([
        'webpush.vapid.public_key' => null,
        'webpush.vapid.private_key' => null,
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('browserPushAvailable', false));
});

test('user can mark own notification as read', function () {
    $user = User::factory()->create();

    $user->notifyNow(new DailyMissingPredictionsSummary(3, 2), ['database']);
    $notification = $user->notifications()->first();

    $this->actingAs($user)
        ->patch(route('notifications.read', $notification))
        ->assertRedirect();

    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('user cannot mark another user notification as read', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $otherUser->notifyNow(new DailyMissingPredictionsSummary(3, 2), ['database']);
    $notification = $otherUser->notifications()->first();

    $this->actingAs($user)
        ->patch(route('notifications.read', $notification))
        ->assertNotFound();

    expect($notification->fresh()->read_at)->toBeNull();
});

test('user can mark all own notifications as read', function () {
    $user = User::factory()->create();

    $user->notifyNow(new DailyMissingPredictionsSummary(3, 2), ['database']);
    $user->notifyNow(new DailyMissingPredictionsSummary(4, 1), ['database']);

    $this->actingAs($user)
        ->patch(route('notifications.read-all'))
        ->assertRedirect();

    expect($user->unreadNotifications()->count())->toBe(0);
});
