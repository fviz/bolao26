<?php

use App\Models\User;
use App\Notifications\TestUserNotification;
use Illuminate\Support\Facades\Notification;

test('send test notification command notifies only the specified user', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'target@example.com']);
    $otherUser = User::factory()->create(['email' => 'other@example.com']);

    $this->artisan('notifications:send-test', [
        'email' => 'target@example.com',
        'body' => 'Mensagem de teste',
    ])->assertSuccessful();

    Notification::assertSentTo($user, TestUserNotification::class, function (TestUserNotification $notification): bool {
        expect($notification->body)->toBe('Mensagem de teste');

        return true;
    });

    Notification::assertNotSentTo($otherUser, TestUserNotification::class);
    Notification::assertCount(1);
});

test('send test notification command fails when user is not found', function () {
    Notification::fake();

    $this->artisan('notifications:send-test', [
        'email' => 'missing@example.com',
        'body' => 'Mensagem de teste',
    ])->assertFailed();

    Notification::assertNothingSent();
});

test('send test notification command stores notification in user inbox', function () {
    $user = User::factory()->create(['email' => 'target@example.com']);
    $otherUser = User::factory()->create(['email' => 'other@example.com']);

    $this->artisan('notifications:send-test', [
        'email' => 'target@example.com',
        'body' => 'Mensagem de teste',
    ])->assertSuccessful();

    $notification = $user->notifications()->first();

    expect($user->notifications()->count())->toBe(1)
        ->and($notification->data['type'])->toBe('test_notification')
        ->and($notification->data['title'])->toBe('Notificação de teste')
        ->and($notification->data['body'])->toBe('Mensagem de teste')
        ->and($notification->data['url'])->toBe(route('notifications.index', absolute: false))
        ->and($otherUser->notifications()->count())->toBe(0);
});
