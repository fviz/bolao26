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

test('rules page renders the rules component', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('rules'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Rules')
            ->missing('sidebarOpen'));
});
