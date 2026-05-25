<?php

namespace App\Support;

use Illuminate\Support\Str;

final class WorldCupPlayers
{
    /**
     * @return list<array{id: string, name: string, position: string, club: string, country: string, countryKey: string}>
     */
    public static function all(): array
    {
        static $players = null;

        if ($players !== null) {
            return $players;
        }

        $players = [];

        foreach (self::squads() as $countryKey => $squad) {
            foreach ($squad['players'] as $row) {
                $name = $row['name'];
                $players[] = [
                    'id' => $countryKey.'-'.Str::slug($name),
                    'name' => $name,
                    'position' => $row['position'],
                    'club' => $row['club'] ?? '',
                    'country' => $squad['country'],
                    'countryKey' => $countryKey,
                ];
            }
        }

        usort($players, function (array $a, array $b): int {
            $country = strcmp($a['country'], $b['country']);

            return $country !== 0 ? $country : strcmp($a['name'], $b['name']);
        });

        return $players;
    }

    /**
     * @return array<string, array{country: string, players: list<array{name: string, position: string, club?: string}>}>
     */
    private static function squads(): array
    {
        return require base_path('app/Data/world-cup-2026-players.php');
    }

    /**
     * @return list<string>
     */
    public static function ids(): array
    {
        return array_column(self::all(), 'id');
    }

    /**
     * @return array{id: string, name: string, position: string, club: string, country: string, countryKey: string}|null
     */
    public static function find(string $id): ?array
    {
        foreach (self::all() as $player) {
            if ($player['id'] === $id) {
                return $player;
            }
        }

        return null;
    }

    /**
     * @return list<array{id: string, name: string, position: string, club: string, country: string, countryKey: string}>
     */
    public static function forFrontend(): array
    {
        return self::all();
    }

    /**
     * @return array<string, list<string>>
     */
    public static function countrySearchTerms(): array
    {
        return [
            'austria' => ['Austria', 'Áustria'],
            'belgium' => ['Belgium', 'Bélgica', 'Belgica'],
            'bosnia_herzegovina' => ['Bosnia & Herzegovina', 'Bosnia and Herzegovina', 'Bósnia', 'Bosnia', 'Bosnia Herzegovina'],
            'brazil' => ['Brazil', 'Brasil'],
            'cape_verde' => ['Cape Verde', 'Cabo Verde'],
            'croatia' => ['Croatia', 'Croácia', 'Croacia'],
            'curacao' => ['Curaçao', 'Curacao'],
            'dr_congo' => ['DR Congo', 'Congo', 'Congo DR', 'República Democrática do Congo', 'RD Congo'],
            'england' => ['England', 'Inglaterra'],
            'france' => ['France', 'França', 'Franca'],
            'germany' => ['Germany', 'Alemanha', 'Alemanha'],
            'haiti' => ['Haiti', 'Haiti'],
            'ivory_coast' => ['Ivory Coast', 'Costa do Marfim', 'Côte d\'Ivoire', 'Cote d\'Ivoire'],
            'japan' => ['Japan', 'Japão', 'Japao'],
            'new_zealand' => ['New Zealand', 'Nova Zelândia', 'Nova Zelandia'],
            'norway' => ['Norway', 'Noruega'],
            'portugal' => ['Portugal'],
            'scotland' => ['Scotland', 'Escócia', 'Escocia'],
            'south_korea' => ['South Korea', 'Korea Republic', 'Coreia do Sul', 'Coreia'],
            'switzerland' => ['Switzerland', 'Suíça', 'Suica'],
            'sweden' => ['Sweden', 'Suécia', 'Suecia'],
            'tunisia' => ['Tunisia', 'Tunísia', 'Tunisia'],
        ];
    }
}
