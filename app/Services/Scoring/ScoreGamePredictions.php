<?php

namespace App\Services\Scoring;

use App\Models\Game;
use App\Models\Prediction;
use App\Notifications\GameFinishedPredictionScored;
use App\Support\NotificationDispatcher;
use Illuminate\Support\Facades\DB;

class ScoreGamePredictions
{
    public function __construct(
        private readonly MatchScoreCalculator $calculator,
        private readonly NotificationDispatcher $notifications,
    ) {}

    public function score(Game $game): bool
    {
        if (! $game->isReadyForScoring()) {
            return false;
        }

        $scoredPredictions = [];

        DB::transaction(function () use ($game, &$scoredPredictions): void {
            $game->predictions()
                ->with('user')
                ->each(function (Prediction $prediction) use ($game, &$scoredPredictions): void {
                    $this->scorePrediction($game, $prediction);
                    $scoredPredictions[] = $prediction->fresh(['game', 'user']);
                });

            $game->scored_at = now();
            $game->save();
        });

        foreach ($scoredPredictions as $prediction) {
            if (! $prediction instanceof Prediction || ! $prediction->user->notificationPreference()->firstOrCreate()->game_result_notifications_enabled) {
                continue;
            }

            $this->notifications->sendOnce(
                $prediction->user,
                'game_finished_prediction_scored',
                "game-result:{$prediction->user_id}:{$game->id}",
                new GameFinishedPredictionScored($game, $prediction->points ?? 0),
            );
        }

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
