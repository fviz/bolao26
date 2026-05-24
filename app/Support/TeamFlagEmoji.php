<?php

namespace App\Support;

class TeamFlagEmoji
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

        $alpha2 = self::toAlpha2(strtoupper($code));

        if ($alpha2 === null) {
            return null;
        }

        return self::emojiFromAlpha2($alpha2);
    }

    public static function toAlpha2(string $code): ?string
    {
        if (strlen($code) === 2 && ctype_alpha($code)) {
            return $code;
        }

        if (strlen($code) !== 3 || ! ctype_alpha($code)) {
            return null;
        }

        /** @var array<string, string> $map */
        $map = config('fifa.country_alpha2', []);

        return $map[$code] ?? null;
    }

    public static function emojiFromAlpha2(string $alpha2): ?string
    {
        if (strlen($alpha2) !== 2 || ! ctype_alpha($alpha2)) {
            return null;
        }

        $alpha2 = strtoupper($alpha2);
        $emoji = '';

        foreach (str_split($alpha2) as $char) {
            $emoji .= mb_chr(0x1F1E6 + ord($char) - ord('A'));
        }

        return $emoji;
    }
}
