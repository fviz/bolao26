<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get(route('rules'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the rules page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('rules'));
    $response->assertOk();
});
