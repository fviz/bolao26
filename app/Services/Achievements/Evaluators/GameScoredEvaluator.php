<?php

namespace App\Services\Achievements\Evaluators;

use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use App\Services\Achievements\AchievementAwarder;
use App\Services\Achievements\AchievementProgressTracker;
use App\Services\Achievements\Support\BrazilMatch;
use App\Services\Achievements\Support\PredictionOutcome;
use App\Services\Achievements\UserStreakCalculator;
use Carbon\CarbonInterface;

class GameScoredEvaluator
{
    public function __construct(
        private readonly AchievementAwarder $awarder,
        private readonly AchievementProgressTracker $progress,
        private readonly UserStreakCalculator $streaks,
    ) {}

    public function evaluate(
        User $user,
        Game $game,
        Prediction $prediction,
        int $newPoints,
        int $oldPoints,
        bool $notify = true,
    ): void {
        $awardedAt = $game->scored_at ?? $game->scheduled_at ?? now();

        if ($newPoints > 0 && ! $this->awarder->has($user, 'saindo-do-zero')) {
            $hadPriorScoringPoints = Prediction::query()
                ->where('user_id', $user->id)
                ->where('points', '>', 0)
                ->whereHas('game', fn ($query) => $query->where('scheduled_at', '<', $game->scheduled_at))
                ->exists();

            if (! $hadPriorScoringPoints) {
                $this->award($user, 'saindo-do-zero', [
                    'game_id' => $game->id,
                    'awarded_at' => $awardedAt,
                ], $notify);
            }
        }

        $this->evaluatePointAchievements($user, $game, $prediction, $newPoints, $awardedAt, $notify);
        $this->evaluateStreakAchievements($user, $awardedAt, $notify);
    }

    public function recalculateStreaks(User $user, bool $notify = true): void
    {
        $this->evaluateStreakAchievements($user, now(), $notify);
    }

    private function evaluatePointAchievements(
        User $user,
        Game $game,
        Prediction $prediction,
        int $points,
        CarbonInterface $awardedAt,
        bool $notify,
    ): void {
        $context = [
            'game_id' => $game->id,
            'awarded_at' => $awardedAt,
        ];

        if ($points === 200) {
            $this->award($user, 'na-gaveta', $context, $notify);
        }

        if ($points === 100) {
            $this->award($user, 'diplomata', $context, $notify);
        }

        if ($points === 95) {
            $this->award($user, 'mae-dinah', $context, $notify);
        }

        if ($points === 220) {
            $this->award($user, 'frieza-total', $context, $notify);
        }

        if ($points === 50) {
            $this->award($user, 'morreu-na-praia', $context, $notify);
        }

        if (
            $game->wentToPenalties()
            && $prediction->home_score === $prediction->away_score
            && $prediction->penalty_winner !== null
        ) {
            $this->award($user, 'coracao-de-aco', $context, $notify);
        }

        if (
            PredictionOutcome::isNearMiss(
                $prediction->home_score,
                $prediction->away_score,
                $game->home_score,
                $game->away_score,
            )
        ) {
            $this->award($user, 'no-quase', $context, $notify);
        }

        if (
            PredictionOutcome::isMirrorScore(
                $prediction->home_score,
                $prediction->away_score,
                $game->home_score,
                $game->away_score,
            )
        ) {
            $this->award($user, 'efeito-espelho', $context, $notify);
        }

        if (
            $prediction->home_score === 0
            && $prediction->away_score === 0
            && ($game->home_score + $game->away_score) >= 4
        ) {
            $this->award($user, 'inocente', $context, $notify);
        }

        if (
            BrazilMatch::predictedBrazilToLose($game, $prediction)
            && BrazilMatch::brazilLost($game)
        ) {
            $this->award($user, 'traidor-da-patria', $context, $notify);
        }
    }

    private function evaluateStreakAchievements(User $user, CarbonInterface $awardedAt, bool $notify): void
    {
        $streaks = $this->streaks->current($user);
        $context = ['awarded_at' => $awardedAt];

        $this->progress->set($user, 'no-embalo', min($streaks['scoringStreak'], 2));
        $this->progress->set($user, 'hat-trick', min($streaks['scoringStreak'], 3));
        $this->progress->set($user, 'em-chamas', min($streaks['scoringStreak'], 5));
        $this->progress->set($user, 'iluminado', min($streaks['exactStreak'], 2));
        $this->progress->set($user, 'leigo-da-bola', min($streaks['wrongResultStreak'], 2));

        if ($streaks['scoringStreak'] >= 2) {
            $this->award($user, 'no-embalo', $context, $notify);
        }

        if ($streaks['scoringStreak'] >= 3) {
            $this->award($user, 'hat-trick', $context, $notify);
        }

        if ($streaks['scoringStreak'] >= 5) {
            $this->award($user, 'em-chamas', $context, $notify);
        }

        if ($streaks['exactStreak'] >= 2) {
            $this->award($user, 'iluminado', $context, $notify);
        }

        if ($streaks['wrongResultStreak'] >= 2) {
            $this->award($user, 'leigo-da-bola', $context, $notify);
        }
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function award(User $user, string $slug, array $context, bool $notify): void
    {
        $this->awarder->award($user, $slug, $context, $notify);
    }
}
