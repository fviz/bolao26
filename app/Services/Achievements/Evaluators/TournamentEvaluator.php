<?php

namespace App\Services\Achievements\Evaluators;

use App\Models\User;
use App\Services\Achievements\AchievementAwarder;

class TournamentEvaluator
{
    private const int CorrectPoints = 300;

    public function __construct(
        private readonly AchievementAwarder $awarder,
    ) {}

    public function evaluateChampion(User $user, bool $notify = true): void
    {
        $championPrediction = $user->championPrediction;

        if ($championPrediction !== null && ($championPrediction->points ?? 0) === self::CorrectPoints) {
            $this->awarder->award($user, 'o-profeta', [
                'awarded_at' => $championPrediction->scored_at ?? now(),
            ], $notify);
        }

        $this->evaluateDonoDaCopa($user, $notify);
    }

    public function evaluateTopScorer(User $user, bool $notify = true): void
    {
        $topScorerPrediction = $user->topScorerPrediction;

        if ($topScorerPrediction !== null && ($topScorerPrediction->points ?? 0) === self::CorrectPoints) {
            $this->awarder->award($user, 'olheiro-de-elite', [
                'awarded_at' => $topScorerPrediction->scored_at ?? now(),
            ], $notify);
        }

        $this->evaluateDonoDaCopa($user, $notify);
    }

    private function evaluateDonoDaCopa(User $user, bool $notify): void
    {
        $championCorrect = ($user->championPrediction?->points ?? 0) === self::CorrectPoints;
        $topScorerCorrect = ($user->topScorerPrediction?->points ?? 0) === self::CorrectPoints;

        if ($championCorrect && $topScorerCorrect) {
            $awardedAt = collect([
                $user->championPrediction?->scored_at,
                $user->topScorerPrediction?->scored_at,
            ])->filter()->max() ?? now();

            $this->awarder->award($user, 'dono-da-copa', [
                'awarded_at' => $awardedAt,
            ], $notify);
        }
    }
}
