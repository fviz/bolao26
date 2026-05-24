<?php

namespace App\Services\Scoring;

use App\Models\Game;
use App\Models\Prediction;
use Illuminate\Support\Facades\DB;

class ScoreGamePredictions
{
    public function __construct(
        private readonly MatchScoreCalculator $calculator,
    ) {}

    public function score(Game $game): bool
    {
        if (! $game->isReadyForScoring()) {
            return false;
        }

        DB::transaction(function () use ($game): void {
            $game->predictions()
                ->with('user')
                ->each(function (Prediction $prediction) use ($game): void {
                    $this->scorePrediction($game, $prediction);
                });

            $game->scored_at = now();
            $game->save();
        });

        return true;
    }

    private function scorePrediction(Game $game, Prediction $prediction): void
    {
        $newPoints = $this->calculator->calculate(
            isKnockout: $game->isKnockout(),
            wentToPenalties: $game->wentToPenalties(),
            actualPenaltyWinner: $game->penaltyWinnerSide(),
            actualHome: $game->home_score,
            actualAway: $game->away_score,
            predictedHome: $prediction->home_score,
            predictedAway: $prediction->away_score,
            predictedPenaltyWinner: $prediction->penalty_winner,
        );

        $oldPoints = $prediction->points ?? 0;
        $delta = $newPoints - $oldPoints;

        if ($delta !== 0) {
            $prediction->user->increment('total_points', $delta);
        }

        $prediction->points = $newPoints;
        $prediction->scored_at = now();
        $prediction->save();
    }
}
