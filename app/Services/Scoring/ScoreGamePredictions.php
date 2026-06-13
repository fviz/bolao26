<?php

namespace App\Services\Scoring;

use App\Models\Game;
use App\Models\Prediction;
use App\Notifications\GameFinishedPredictionScored;
use App\Services\Achievements\AchievementAwarder;
use App\Services\Achievements\Evaluators\DayCompleteEvaluator;
use App\Services\Achievements\Evaluators\GameScoredEvaluator;
use App\Services\Achievements\Evaluators\GameSocialEvaluator;
use App\Support\NotificationDispatcher;
use Illuminate\Support\Facades\DB;

class ScoreGamePredictions
{
    public function __construct(
        private readonly MatchScoreCalculator $calculator,
        private readonly NotificationDispatcher $notifications,
        private readonly AchievementAwarder $achievementAwarder,
        private readonly GameScoredEvaluator $gameScoredAchievements,
        private readonly GameSocialEvaluator $gameSocialAchievements,
        private readonly DayCompleteEvaluator $dayCompleteAchievements,
    ) {}

    public function score(Game $game): bool
    {
        if (! $game->isReadyForScoring()) {
            return false;
        }

        $this->achievementAwarder->beginBatch();

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

        $this->gameSocialAchievements->evaluate($game);
        $this->dayCompleteAchievements->evaluateForGame($game);

        $this->achievementAwarder->flushBatches();

        return true;
    }

    public function unscore(Game $game): bool
    {
        if ($game->scored_at === null) {
            return false;
        }

        DB::transaction(function () use ($game): void {
            $game->predictions()
                ->with('user')
                ->each(function (Prediction $prediction): void {
                    $this->unscorePrediction($prediction);
                });

            $game->scored_at = null;
            $game->save();
        });

        return true;
    }

    private function unscorePrediction(Prediction $prediction): void
    {
        $oldPoints = $prediction->points ?? 0;

        if ($oldPoints !== 0) {
            $prediction->user->decrement('total_points', $oldPoints);
        }

        $prediction->points = null;
        $prediction->scored_at = null;
        $prediction->save();

        $this->gameScoredAchievements->recalculateStreaks($prediction->user);
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

        $this->gameScoredAchievements->evaluate(
            $prediction->user,
            $game,
            $prediction,
            $newPoints,
            $oldPoints,
        );
    }
}
