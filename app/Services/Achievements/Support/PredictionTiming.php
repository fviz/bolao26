<?php

namespace App\Services\Achievements\Support;

use App\Models\Game;
use Carbon\CarbonInterface;

class PredictionTiming
{
    public const int LATE_BET_MINUTES = 10;

    public static function isLateBet(Game $game, CarbonInterface $placedAt): bool
    {
        if ($placedAt->greaterThanOrEqualTo($game->scheduled_at)) {
            return false;
        }

        $kickoffWindowStart = $game->scheduled_at->copy()->subMinutes(self::LATE_BET_MINUTES);

        return $placedAt->greaterThan($kickoffWindowStart);
    }
}
