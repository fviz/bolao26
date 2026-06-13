<?php

namespace App\Services\Achievements;

use App\Models\Prediction;
use App\Models\User;

class UserStreakCalculator
{
    /**
     * @return array{scoringStreak: int, exactStreak: int}
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

        foreach ($predictions as $prediction) {
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
        }

        return [
            'scoringStreak' => $currentScoring,
            'exactStreak' => $currentExact,
        ];
    }
}
