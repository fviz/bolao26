<?php

namespace App\Services\Achievements\Evaluators;

use App\Models\User;
use App\Services\Achievements\AchievementAwarder;
use App\Services\Achievements\AchievementProgressTracker;

class CommentEvaluator
{
    private const int FALADOR_TARGET = 5;

    public function __construct(
        private readonly AchievementAwarder $awarder,
        private readonly AchievementProgressTracker $progress,
    ) {}

    public function evaluate(User $user, bool $notify = true): void
    {
        $count = $user->gameComments()->count();

        $this->progress->set($user, 'falador', min($count, self::FALADOR_TARGET));

        if ($count >= self::FALADOR_TARGET) {
            $this->awarder->award($user, 'falador', [
                'awarded_at' => now(),
            ], $notify);
        }
    }
}
