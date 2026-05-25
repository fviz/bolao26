<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('public');
});

test('user can upload a profile avatar', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('profile.avatar.store'), [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->avatar_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar_path);
    expect($user->avatar)->toContain('/storage/');
});

test('uploading a new avatar replaces the previous file', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->post(route('profile.avatar.store'), [
            'avatar' => UploadedFile::fake()->image('first.jpg'),
        ]);

    $user->refresh();
    $firstPath = $user->avatar_path;

    $this
        ->actingAs($user)
        ->post(route('profile.avatar.store'), [
            'avatar' => UploadedFile::fake()->image('second.jpg'),
        ]);

    $user->refresh();

    expect($user->avatar_path)->not->toBe($firstPath);
    Storage::disk('public')->assertMissing($firstPath);
    Storage::disk('public')->assertExists($user->avatar_path);
});

test('avatar upload rejects files larger than 5MB', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->post(route('profile.avatar.store'), [
            'avatar' => UploadedFile::fake()->image('large.jpg')->size(5121),
        ]);

    $response
        ->assertSessionHasErrors('avatar')
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->avatar_path)->toBeNull();
});

test('avatar upload rejects non-image files', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->post(route('profile.avatar.store'), [
            'avatar' => UploadedFile::fake()->create('document.pdf', 100),
        ]);

    $response
        ->assertSessionHasErrors('avatar')
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->avatar_path)->toBeNull();
});

test('user can remove their profile avatar', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->post(route('profile.avatar.store'), [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

    $user->refresh();
    $path = $user->avatar_path;

    $response = $this
        ->actingAs($user)
        ->delete(route('profile.avatar.destroy'));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->avatar_path)->toBeNull();
    expect($user->avatar)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('deleting account removes the avatar file from storage', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->post(route('profile.avatar.store'), [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

    $user->refresh();
    $path = $user->avatar_path;

    $this
        ->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    Storage::disk('public')->assertMissing($path);
});

test('profile page includes avatar url after upload', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->post(route('profile.avatar.store'), [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

    $user->refresh();

    $this
        ->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('auth.user.avatar', $user->avatar)
        );
});
