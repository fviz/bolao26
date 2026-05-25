<?php

namespace App\Support;

class TeamFlagIcon
{
    /**
     * @param  array<string, mixed>|null  $payloadSide
     */
    public static function forTeam(?string $abbreviation, ?array $payloadSide = null): ?string
    {
        $code = $abbreviation;

        if ($code === null && is_array($payloadSide)) {
            $code = isset($payloadSide['IdCountry'])
                ? (string) $payloadSide['IdCountry']
                : null;
        }

        if ($code === null || $code === '') {
            return null;
        }

        $teamCode = strtoupper($code);

        /** @var array<string, string> $iconCodes */
        $iconCodes = config('fifa.flag_icon_codes', []);

        if (isset($iconCodes[$teamCode])) {
            return self::normalizeFlagIconCode($iconCodes[$teamCode]);
        }

        $alpha2 = self::toAlpha2($teamCode);

        if ($alpha2 === null) {
            return null;
        }

        return strtolower($alpha2);
    }

    public static function toAlpha2(string $code): ?string
    {
        if (strlen($code) === 2 && ctype_alpha($code)) {
            return strtoupper($code);
        }

        if (strlen($code) !== 3 || ! ctype_alpha($code)) {
            return null;
        }

        /** @var array<string, string> $map */
        $map = config('fifa.country_alpha2', []);

        return $map[$code] ?? null;
    }

    private static function normalizeFlagIconCode(string $code): ?string
    {
        $code = strtolower($code);

        if (preg_match('/^[a-z]{2}(?:-[a-z0-9]+)?$/', $code) !== 1) {
            return null;
        }

        return $code;
    }
}
