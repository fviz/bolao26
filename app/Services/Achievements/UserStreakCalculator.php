<?php

namespace App\Services\Achievements;

use App\Models\Prediction;
use App\Models\User;
use App\Services\Achievements\Support\PredictionOutcome;

class UserStreakCalculator
{
    /**
     * @return array{scoringStreak: int, exactStreak: int, wrongResultStreak: int}
     */
    public function current(User $user): array
    {
        $predictions = Prediction::query()
            ->where('user_id', $user->id)
            ->whereNotNull('points')
            ->whereHas('game', fn ($query) => $query->finished())
            ->with('game')
            ->get()
            ->sortBy(fn (Prediction $prediction) => $prediction->game->scheduled_at?->timestamp ?? 0)
            ->values();

        $currentScoring = 0;
        $currentExact = 0;
        $currentWrongResult = 0;

        foreach ($predictions as $prediction) {
            $game = $prediction->game;
            $points = $prediction->points ?? 0;

            if ($points > 0) {
                $currentScoring++;
            } else {
                $currentScoring = 0;
            }

            if ($points === 200) {
                $currentExact++;
            } else {
                $currentExact = 0;
            }

            if (
                $game !== null
                && PredictionOutcome::hasWrongResult(
                    $prediction->home_score,
                    $prediction->away_score,
                    $game->home_score,
                    $game->away_score,
                )
            ) {
                $currentWrongResult++;
            } else {
                $currentWrongResult = 0;
            }
        }

        return [
            'scoringStreak' => $currentScoring,
            'exactStreak' => $currentExact,
            'wrongResultStreak' => $currentWrongResult,
        ];
    }
}
