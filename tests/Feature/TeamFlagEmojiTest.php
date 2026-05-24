<?php

use App\Support\TeamFlagEmoji;

test('team flag emoji maps fifa codes to regional indicators', function () {
    expect(TeamFlagEmoji::forTeam('MEX'))->toBe('🇲🇽')
        ->and(TeamFlagEmoji::forTeam('RSA'))->toBe('🇿🇦')
        ->and(TeamFlagEmoji::forTeam('KOR'))->toBe('🇰🇷');
});

test('team flag emoji returns null for unknown codes', function () {
    expect(TeamFlagEmoji::forTeam('XYZ'))->toBeNull();
});
