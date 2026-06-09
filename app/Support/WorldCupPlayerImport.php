<?php

namespace App\Support;

use Illuminate\Support\Str;
use JsonException;

final class WorldCupPlayerImport
{
    /**
     * @var array<string, array{key: string, display: string}>
     */
    private const COUNTRY_MAP = [
        'Algeria' => ['key' => 'algeria', 'display' => 'Algeria'],
        'Argentina' => ['key' => 'argentina', 'display' => 'Argentina'],
        'Australia' => ['key' => 'australia', 'display' => 'Australia'],
        'Austria' => ['key' => 'austria', 'display' => 'Austria'],
        'Belgium' => ['key' => 'belgium', 'display' => 'Belgium'],
        'Bosnia And Herzegovina' => ['key' => 'bosnia_herzegovina', 'display' => 'Bosnia & Herzegovina'],
        'Brazil' => ['key' => 'brazil', 'display' => 'Brazil'],
        'Cabo Verde' => ['key' => 'cape_verde', 'display' => 'Cape Verde'],
        'Canada' => ['key' => 'canada', 'display' => 'Canada'],
        'Colombia' => ['key' => 'colombia', 'display' => 'Colombia'],
        'Congo DR' => ['key' => 'dr_congo', 'display' => 'DR Congo'],
        "Côte D'Ivoire" => ['key' => 'ivory_coast', 'display' => 'Ivory Coast'],
        'Croatia' => ['key' => 'croatia', 'display' => 'Croatia'],
        'Curaçao' => ['key' => 'curacao', 'display' => 'Curaçao'],
        'Czechia' => ['key' => 'czechia', 'display' => 'Czechia'],
        'Ecuador' => ['key' => 'ecuador', 'display' => 'Ecuador'],
        'Egypt' => ['key' => 'egypt', 'display' => 'Egypt'],
        'England' => ['key' => 'england', 'display' => 'England'],
        'France' => ['key' => 'france', 'display' => 'France'],
        'Germany' => ['key' => 'germany', 'display' => 'Germany'],
        'Ghana' => ['key' => 'ghana', 'display' => 'Ghana'],
        'Haiti' => ['key' => 'haiti', 'display' => 'Haiti'],
        'IR Iran' => ['key' => 'ir_iran', 'display' => 'Iran'],
        'Iraq' => ['key' => 'iraq', 'display' => 'Iraq'],
        'Japan' => ['key' => 'japan', 'display' => 'Japan'],
        'Jordan' => ['key' => 'jordan', 'display' => 'Jordan'],
        'Korea Republic' => ['key' => 'south_korea', 'display' => 'South Korea'],
        'Mexico' => ['key' => 'mexico', 'display' => 'Mexico'],
        'Morocco' => ['key' => 'morocco', 'display' => 'Morocco'],
        'Netherlands' => ['key' => 'netherlands', 'display' => 'Netherlands'],
        'New Zealand' => ['key' => 'new_zealand', 'display' => 'New Zealand'],
        'Norway' => ['key' => 'norway', 'display' => 'Norway'],
        'Panama' => ['key' => 'panama', 'display' => 'Panama'],
        'Paraguay' => ['key' => 'paraguay', 'display' => 'Paraguay'],
        'Portugal' => ['key' => 'portugal', 'display' => 'Portugal'],
        'Qatar' => ['key' => 'qatar', 'display' => 'Qatar'],
        'Saudi Arabia' => ['key' => 'saudi_arabia', 'display' => 'Saudi Arabia'],
        'Scotland' => ['key' => 'scotland', 'display' => 'Scotland'],
        'Senegal' => ['key' => 'senegal', 'display' => 'Senegal'],
        'South Africa' => ['key' => 'south_africa', 'display' => 'South Africa'],
        'Spain' => ['key' => 'spain', 'display' => 'Spain'],
        'Sweden' => ['key' => 'sweden', 'display' => 'Sweden'],
        'Switzerland' => ['key' => 'switzerland', 'display' => 'Switzerland'],
        'Tunisia' => ['key' => 'tunisia', 'display' => 'Tunisia'],
        'Türkiye' => ['key' => 'turkiye', 'display' => 'Türkiye'],
        'Uruguay' => ['key' => 'uruguay', 'display' => 'Uruguay'],
        'USA' => ['key' => 'usa', 'display' => 'USA'],
        'Uzbekistan' => ['key' => 'uzbekistan', 'display' => 'Uzbekistan'],
    ];

    /**
     * @return array<string, array{country: string, players: list<array{name: string, position: string, club: string}>}>
     */
    public static function squadsFromJsonFile(string $path): array
    {
        if (! is_readable($path)) {
            throw new JsonException("Cannot read player data file: {$path}");
        }

        $raw = file_get_contents($path);

        if ($raw === false) {
            throw new JsonException("Cannot read player data file: {$path}");
        }

        try {
            /** @var array<string, list<array{player_name: string, position: string, club: string}>> $data */
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new JsonException("Invalid JSON in player data file: {$exception->getMessage()}", 0, $exception);
        }

        $squads = [];

        foreach ($data as $jsonCountry => $players) {
            $country = self::resolveCountry($jsonCountry);

            $squads[$country['key']] = [
                'country' => $country['display'],
                'players' => array_map(
                    fn (array $player): array => [
                        'name' => self::formatPlayerName($player['player_name']),
                        'position' => self::formatPosition($player['position']),
                        'club' => self::formatClub($player['club']),
                    ],
                    $players,
                ),
            ];
        }

        ksort($squads);

        return $squads;
    }

    /**
     * @return array{key: string, display: string}
     */
    public static function resolveCountry(string $jsonCountry): array
    {
        if (isset(self::COUNTRY_MAP[$jsonCountry])) {
            return self::COUNTRY_MAP[$jsonCountry];
        }

        return [
            'key' => Str::snake($jsonCountry),
            'display' => $jsonCountry,
        ];
    }

    public static function formatPlayerName(string $raw): string
    {
        $tokens = preg_split('/\s+/', trim($raw)) ?: [];

        if ($tokens === []) {
            return '';
        }

        $firstGivenIndex = null;

        foreach ($tokens as $index => $token) {
            if (preg_match('/[a-z]/', $token) === 1) {
                $firstGivenIndex = $index;
                break;
            }
        }

        if ($firstGivenIndex !== null && $firstGivenIndex > 0) {
            $surname = implode(' ', array_slice($tokens, 0, $firstGivenIndex));
            $givenTokens = array_slice($tokens, $firstGivenIndex);

            return self::titleCase($givenTokens[0]).' '.self::titleCase($surname);
        }

        if (count($tokens) === 1) {
            return self::titleCase($tokens[0]);
        }

        return implode(' ', array_map(self::titleCase(...), $tokens));
    }

    public static function formatPosition(string $position): string
    {
        return match (strtoupper($position)) {
            'GK' => 'Goalkeeper',
            'DF' => 'Defender',
            'MF' => 'Midfielder',
            'FW' => 'Forward',
            default => $position,
        };
    }

    public static function formatClub(string $club): string
    {
        $club = trim($club);
        $club = preg_replace('/\s+\([A-Z]{3}\)$/', '', $club) ?? $club;
        $club = preg_replace('/\s+(FC|FK)$/', '', $club) ?? $club;

        return trim($club);
    }

    public static function playerId(string $countryKey, string $name): string
    {
        return $countryKey.'-'.Str::slug($name);
    }

    private static function titleCase(string $value): string
    {
        $lower = mb_strtolower($value, 'UTF-8');

        return mb_convert_case($lower, MB_CASE_TITLE, 'UTF-8');
    }
}
