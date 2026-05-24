<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Features;

uses(RefreshDatabase::class);

test('validation messages use brazilian portuguese', function () {
    $validator = Validator::make(
        ['password' => 'short'],
        ['password' => ['required', 'string', Password::min(12)]],
        [],
        ['password' => 'senha'],
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('password'))
        ->toBe('O campo senha deve conter no mínimo 12 caracteres.');
});

test('registration returns portuguese password validation errors', function () {
    $this->skipUnlessFortifyHas(Features::registration());

    Password::defaults(fn () => Password::min(12));

    $response = $this->post(route('register.store'), [
        'invite_code' => 'test-invite-code',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertSessionHasErrors('password');
    expect(session('errors')->get('password')[0])
        ->toContain('no mínimo 12 caracteres');
});
