<?php

use App\Support\WorldCupPlayerImport;

test('formats european style player names', function () {
    expect(WorldCupPlayerImport::formatPlayerName('HAALAND Erling'))->toBe('Erling Haaland')
        ->and(WorldCupPlayerImport::formatPlayerName('GUEHI Marc Addji Keaninkin'))->toBe('Marc Guehi');
});

test('formats all caps player names', function () {
    expect(WorldCupPlayerImport::formatPlayerName('ALISSON'))->toBe('Alisson')
        ->and(WorldCupPlayerImport::formatPlayerName('GABRIEL MAGALHAES'))->toBe('Gabriel Magalhaes')
        ->and(WorldCupPlayerImport::formatPlayerName('NEYMAR JR'))->toBe('Neymar Jr');
});

test('formats positions and clubs', function () {
    expect(WorldCupPlayerImport::formatPosition('GK'))->toBe('Goalkeeper')
        ->and(WorldCupPlayerImport::formatPosition('DF'))->toBe('Defender')
        ->and(WorldCupPlayerImport::formatPosition('MF'))->toBe('Midfielder')
        ->and(WorldCupPlayerImport::formatPosition('FW'))->toBe('Forward')
        ->and(WorldCupPlayerImport::formatClub('Manchester City FC (ENG)'))->toBe('Manchester City');
});

test('builds stable player ids for known picks', function () {
    expect(WorldCupPlayerImport::playerId('norway', 'Erling Haaland'))->toBe('norway-erling-haaland')
        ->and(WorldCupPlayerImport::playerId('brazil', 'Neymar Jr'))->toBe('brazil-neymar-jr');
});

test('maps fifa country names to app country keys', function () {
    expect(WorldCupPlayerImport::resolveCountry('Korea Republic'))->toBe([
        'key' => 'south_korea',
        'display' => 'South Korea',
    ])->and(WorldCupPlayerImport::resolveCountry('Côte D\'Ivoire'))->toBe([
        'key' => 'ivory_coast',
        'display' => 'Ivory Coast',
    ]);
});

test('imports all squads from json source file', function () {
    $squads = WorldCupPlayerImport::squadsFromJsonFile(
        dirname(__DIR__, 2).'/app/Data/updated-player-data.json',
    );

    $playerCount = array_sum(array_map(
        fn (array $squad): int => count($squad['players']),
        $squads,
    ));

    expect($squads)->toHaveCount(48)
        ->and($playerCount)->toBe(1248)
        ->and($squads['norway']['players'])->toContain([
            'name' => 'Erling Haaland',
            'position' => 'Forward',
            'club' => 'Manchester City',
        ]);
});
