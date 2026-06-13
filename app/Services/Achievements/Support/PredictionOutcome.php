<?php

namespace App\Services\Achievements\Support;

use App\Models\Game;
use App\Models\Prediction;

class PredictionOutcome
{
    /**
     * -1 = home win, 0 = draw, 1 = away win
     */
    public static function fromScores(int $home, int $away): int
    {
        if ($home > $away) {
            return -1;
        }

        if ($home < $away) {
            return 1;
        }

        return 0;
    }

    public static function winnerSide(Game $game): ?string
    {
        return $game->matchWinnerSide();
    }

    public static function predictedWinnerSide(Prediction $prediction): ?string
    {
        $outcome = self::fromScores($prediction->home_score, $prediction->away_score);

        if ($outcome === -1) {
            return 'home';
        }

        if ($outcome === 1) {
            return 'away';
        }

        return null;
    }

    public static function isMirrorScore(
        int $predictedHome,
        int $predictedAway,
        int $actualHome,
        int $actualAway,
    ): bool {
        return $predictedHome === $actualAway
            && $predictedAway === $actualHome
            && ($predictedHome !== $actualHome || $predictedAway !== $actualAway);
    }

    public static function isNearMiss(
        int $predictedHome,
        int $predictedAway,
        int $actualHome,
        int $actualAway,
    ): bool {
        if ($predictedHome === $actualHome && $predictedAway === $actualAway) {
            return false;
        }

        $delta = abs($predictedHome - $actualHome) + abs($predictedAway - $actualAway);

        return $delta === 1;
    }
}
