<?php

namespace App\Services\Achievements\Support;

use App\Models\Game;
use App\Models\Prediction;

class BrazilMatch
{
    public static function brazilSide(Game $game): ?string
    {
        if ($game->home_abbr === 'BRA') {
            return 'home';
        }

        if ($game->away_abbr === 'BRA') {
            return 'away';
        }

        return null;
    }

    public static function brazilLost(Game $game): bool
    {
        $brazilSide = self::brazilSide($game);

        if ($brazilSide === null) {
            return false;
        }

        $winner = $game->matchWinnerSide();

        return $winner !== null && $winner !== $brazilSide;
    }

    public static function predictedBrazilToLose(Game $game, Prediction $prediction): bool
    {
        $brazilSide = self::brazilSide($game);

        if ($brazilSide === null) {
            return false;
        }

        $opponentSide = $brazilSide === 'home' ? 'away' : 'home';

        if ($brazilSide === 'home' && $prediction->away_score > $prediction->home_score) {
            return true;
        }

        if ($brazilSide === 'away' && $prediction->home_score > $prediction->away_score) {
            return true;
        }

        if (
            $prediction->home_score === $prediction->away_score
            && $prediction->penalty_winner === $opponentSide
        ) {
            return true;
        }

        return false;
    }
}
