<?php

namespace App\Services\Achievements\Evaluators;

use App\Models\Game;
use App\Models\Prediction;
use App\Services\Achievements\AchievementAwarder;
use App\Services\Achievements\Support\PredictionOutcome;
use Illuminate\Support\Collection;

class GameSocialEvaluator
{
    public function __construct(
        private readonly AchievementAwarder $awarder,
    ) {}

    public function evaluate(Game $game, bool $notify = true): void
    {
        $predictions = $game->predictions()
            ->with('user')
            ->get();

        if ($predictions->isEmpty()) {
            return;
        }

        $awardedAt = $game->scored_at ?? $game->scheduled_at ?? now();
        $context = [
            'game_id' => $game->id,
            'awarded_at' => $awardedAt,
        ];

        $this->evaluateLoboSolitario($predictions, $context, $notify);
        $this->evaluateContraACorrenteza($game, $predictions, $context, $notify);
        $this->evaluateZicouOBonde($game, $predictions, $context, $notify);
    }

    /**
     * @param  Collection<int, Prediction>  $predictions
     * @param  array<string, mixed>  $context
     */
    private function evaluateLoboSolitario($predictions, array $context, bool $notify): void
    {
        $exactScorers = $predictions->filter(fn (Prediction $p) => ($p->points ?? 0) === 200);

        if ($exactScorers->count() !== 1) {
            return;
        }

        $winner = $exactScorers->first();

        if ($winner === null || $winner->user === null) {
            return;
        }

        $this->awarder->award($winner->user, 'lobo-solitario', $context, $notify);
    }

    /**
     * @param  Collection<int, Prediction>  $predictions
     * @param  array<string, mixed>  $context
     */
    private function evaluateContraACorrenteza(Game $game, $predictions, array $context, bool $notify): void
    {
        $total = $predictions->count();

        if ($total === 0) {
            return;
        }

        $winnerSideCounts = ['home' => 0, 'away' => 0, 'draw' => 0];

        foreach ($predictions as $prediction) {
            $side = PredictionOutcome::predictedWinnerSide($prediction);

            if ($side === 'home') {
                $winnerSideCounts['home']++;
            } elseif ($side === 'away') {
                $winnerSideCounts['away']++;
            } else {
                $winnerSideCounts['draw']++;
            }
        }

        foreach ($predictions as $prediction) {
            $points = $prediction->points ?? 0;

            if ($points < 75) {
                continue;
            }

            $predictedSide = PredictionOutcome::predictedWinnerSide($prediction);

            if ($predictedSide === null) {
                continue;
            }

            $pickCount = $winnerSideCounts[$predictedSide];
            $pickPercentage = ($pickCount / $total) * 100;

            if ($pickPercentage >= 20) {
                continue;
            }

            if ($prediction->user === null) {
                continue;
            }

            $this->awarder->award($prediction->user, 'contra-a-correnteza', $context, $notify);
        }
    }

    /**
     * @param  Collection<int, Prediction>  $predictions
     * @param  array<string, mixed>  $context
     */
    private function evaluateZicouOBonde(Game $game, $predictions, array $context, bool $notify): void
    {
        $actualWinner = PredictionOutcome::winnerSide($game);

        if ($actualWinner === null) {
            return;
        }

        $predictedSides = $predictions
            ->map(fn (Prediction $p) => PredictionOutcome::predictedWinnerSide($p))
            ->filter(fn (?string $side) => $side !== null);

        if ($predictedSides->isEmpty()) {
            return;
        }

        $uniqueSides = $predictedSides->unique();

        if ($uniqueSides->count() !== 1) {
            return;
        }

        $unanimousPick = $uniqueSides->first();

        if ($unanimousPick === $actualWinner) {
            return;
        }

        foreach ($predictions as $prediction) {
            if ($prediction->user === null) {
                continue;
            }

            $this->awarder->award($prediction->user, 'zicou-o-bonde', $context, $notify);
        }
    }
}
