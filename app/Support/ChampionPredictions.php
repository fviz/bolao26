<?php

namespace App\Support;

use App\Models\Game;
use Illuminate\Support\Carbon;

final class ChampionPredictions
{
    public static function isOpen(): bool
    {
        return now()->lt(self::deadline());
    }

    public static function deadline(): Carbon
    {
        return Carbon::parse(config('bolao.champion_predictions_deadline'));
    }

    /**
     * @return list<array{fifaTeamId: string, name: string, abbr: string|null}>
     */
    public static function availableTeams(): array
    {
        $teams = [];

        foreach (Game::query()->cursor() as $game) {
            if ($game->home_fifa_team_id !== null && $game->home_name !== null) {
                $teams[$game->home_fifa_team_id] = [
                    'fifaTeamId' => $game->home_fifa_team_id,
                    'name' => $game->home_name,
                    'abbr' => $game->home_abbr,
                ];
            }

            if ($game->away_fifa_team_id !== null && $game->away_name !== null) {
                $teams[$game->away_fifa_team_id] = [
                    'fifaTeamId' => $game->away_fifa_team_id,
                    'name' => $game->away_name,
                    'abbr' => $game->away_abbr,
                ];
            }
        }

        usort($teams, fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return array_values($teams);
    }
}
