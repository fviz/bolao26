<?php

use App\Jobs\BroadcastAdminNotification;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

test('non-admin cannot broadcast notification', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('notifications.broadcast.store'), [
            'title' => 'Test',
            'body' => 'Hello',
        ])
        ->assertForbidden();
});

test('admin can broadcast notification and dispatches job', function () {
    Queue::fake();

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('notifications.broadcast.store'), [
            'title' => 'Aviso',
            'body' => 'Mensagem importante',
            'url' => 'https://example.com',
        ])
        ->assertRedirect();

    Queue::assertPushed(BroadcastAdminNotification::class, function (BroadcastAdminNotification $job): bool {
        expect($job->title)->toBe('Aviso');
        expect($job->body)->toBe('Mensagem importante');
        expect($job->url)->toBe('https://example.com');

        return true;
    });
});

test('broadcast job sends notification to all users', function () {
    $admin = User::factory()->admin()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    (new BroadcastAdminNotification('Title', 'Body', null))->handle();

    expect($admin->notifications()->count())->toBe(1);
    expect($user1->notifications()->count())->toBe(1);
    expect($user2->notifications()->count())->toBe(1);

    $notification = $admin->notifications()->first();

    expect($notification->data['type'])->toBe('admin_broadcast');
    expect($notification->data['title'])->toBe('Title');
    expect($notification->data['body'])->toBe('Body');
});

test('broadcast requires title and body', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('notifications.broadcast.store'), [])
        ->assertSessionHasErrors(['title', 'body']);
});
