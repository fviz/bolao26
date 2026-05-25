<?php

use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Support\TeamFlagIcon;

test('team flag icon maps fifa codes to bundled icon codes', function () {
    expect(TeamFlagIcon::forTeam('MEX'))->toBe('mx')
        ->and(TeamFlagIcon::forTeam('RSA'))->toBe('za')
        ->and(TeamFlagIcon::forTeam('KOR'))->toBe('kr')
        ->and(TeamFlagIcon::forTeam('ENG'))->toBe('gb-eng')
        ->and(TeamFlagIcon::forTeam('WAL'))->toBe('gb-wls')
        ->and(TeamFlagIcon::forTeam('HAI'))->toBe('ht')
        ->and(TeamFlagIcon::forTeam('CUW'))->toBe('cw')
        ->and(TeamFlagIcon::forTeam('CIV'))->toBe('ci')
        ->and(TeamFlagIcon::forTeam('CPV'))->toBe('cv')
        ->and(TeamFlagIcon::forTeam('KSA'))->toBe('sa')
        ->and(TeamFlagIcon::forTeam('IRQ'))->toBe('iq')
        ->and(TeamFlagIcon::forTeam('JOR'))->toBe('jo')
        ->and(TeamFlagIcon::forTeam('COD'))->toBe('cd')
        ->and(TeamFlagIcon::forTeam('UZB'))->toBe('uz');
});

test('team flag icon falls back to fifa payload country id', function () {
    expect(TeamFlagIcon::forTeam(null, ['IdCountry' => 'BRA']))->toBe('br');
});

test('team flag icon accepts alpha two country codes', function () {
    expect(TeamFlagIcon::forTeam('BR'))->toBe('br')
        ->and(TeamFlagIcon::forTeam('za'))->toBe('za');
});

test('team flag icon returns null for unknown codes', function () {
    expect(TeamFlagIcon::forTeam('XYZ'))->toBeNull()
        ->and(TeamFlagIcon::forTeam(''))->toBeNull()
        ->and(TeamFlagIcon::forTeam(null))->toBeNull();
});

test('game resource exposes flag icon codes for teams', function () {
    $game = Game::factory()->make([
        'home_name' => 'England',
        'home_abbr' => 'ENG',
        'away_name' => 'South Africa',
        'away_abbr' => null,
        'payload' => [
            'Away' => [
                'IdCountry' => 'RSA',
            ],
        ],
    ]);

    $payload = GameResource::make($game)->resolve(request());

    expect($payload['home']['flagIconCode'])->toBe('gb-eng')
        ->and($payload['away']['flagIconCode'])->toBe('za');
});
