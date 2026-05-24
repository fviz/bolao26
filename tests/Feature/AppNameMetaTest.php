<?php

test('inertia root template exposes app name for client-side page titles', function () {
    config(['app.name' => 'Bolão 26']);

    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertSee(
        '<meta name="app-name" content="Bolão 26">',
        escape: false,
    );
});
