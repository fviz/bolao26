<?php

namespace App\Services\Achievements\Evaluators;

use App\Models\User;
use App\Services\Achievements\AchievementAwarder;

class ProfileEvaluator
{
    public function __construct(
        private readonly AchievementAwarder $awarder,
    ) {}

    public function evaluate(User $user, bool $notify = true): void
    {
        if ($user->avatar_path === null) {
            return;
        }

        $this->awarder->award($user, 'boa-pinta', [
            'awarded_at' => now(),
        ], $notify);
    }
}
