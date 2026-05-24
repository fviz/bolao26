<?php

use App\Services\Fifa\Exceptions\FifaApiException;
use App\Services\Fifa\FifaCalendarClient;
use Illuminate\Support\Facades\Http;

test('fifa calendar client returns matches from api', function () {
    $payload = json_decode(
        file_get_contents(base_path('tests/Fixtures/fifa/calendar-matches.json')),
        true,
    );

    Http::fake([
        '*' => Http::response($payload),
    ]);

    $matches = app(FifaCalendarClient::class)->matches();

    expect($matches)->toHaveCount(2)
        ->and($matches[0]['IdMatch'])->toBe('400021443');
});

test('fifa calendar client throws when api request fails', function () {
    Http::fake([
        '*' => Http::response(null, 500),
    ]);

    app(FifaCalendarClient::class)->matches();
})->throws(FifaApiException::class);

test('sync fifa games command fails when api request fails', function () {
    Http::fake([
        '*' => Http::response(null, 500),
    ]);

    $this->artisan('games:sync-fifa')
        ->assertFailed();
});
