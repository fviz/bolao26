<?php

namespace App\Support\Achievements;

use App\Models\Game;
use Illuminate\Support\Collection;

final class MatchDayGames
{
    /**
     * @return Collection<int, Game>
     */
    public static function forDate(string $date): Collection
    {
        $games = Game::query()
            ->whereDate('local_scheduled_at', $date)
            ->orderBy('scheduled_at')
            ->get();

        if ($games->isNotEmpty()) {
            return $games;
        }

        return Game::query()
            ->whereDate('scheduled_at', $date)
            ->orderBy('scheduled_at')
            ->get();
    }
}
