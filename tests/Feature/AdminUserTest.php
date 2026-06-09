<?php

use App\Models\User;

test('user:set-admin grants admin status by email', function () {
    $user = User::factory()->create(['email' => 'admin@example.com']);

    $this->artisan('user:set-admin', ['user' => 'admin@example.com'])
        ->assertSuccessful()
        ->expectsOutput("Admin granted to {$user->name} (admin@example.com).");

    expect($user->fresh()->is_admin)->toBeTrue();
});

test('user:set-admin grants admin status by id', function () {
    $user = User::factory()->create();

    $this->artisan('user:set-admin', ['user' => (string) $user->id])
        ->assertSuccessful();

    expect($user->fresh()->is_admin)->toBeTrue();
});

test('user:set-admin revokes admin status', function () {
    $user = User::factory()->admin()->create(['email' => 'admin@example.com']);

    $this->artisan('user:set-admin', ['user' => 'admin@example.com', '--revoke' => true])
        ->assertSuccessful()
        ->expectsOutput("Admin revoked from {$user->name} (admin@example.com).");

    expect($user->fresh()->is_admin)->toBeFalse();
});

test('user:set-admin fails for unknown user', function () {
    $this->artisan('user:set-admin', ['user' => 'missing@example.com'])
        ->assertFailed()
        ->expectsOutput('User not found.');
});
