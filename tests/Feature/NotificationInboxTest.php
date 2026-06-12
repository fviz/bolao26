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
            ->where('notifications.data.0.readAt', null)
            ->where('browserPushAvailable', true),
        );
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
