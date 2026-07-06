<?php

namespace App\Services\Achievements\Evaluators;

use App\Models\User;
use App\Services\Achievements\AchievementAwarder;
use Carbon\CarbonInterface;

class LoginEvaluator
{
    public function __construct(
        private readonly AchievementAwarder $awarder,
    ) {}

    public function evaluate(User $user, CarbonInterface $loggedInAt, bool $notify = true): void
    {
        $localLoginAt = $loggedInAt->copy()->timezone('America/Sao_Paulo');

        if ($localLoginAt->hour >= 0 && $localLoginAt->hour < 6) {
            $this->awarder->award($user, 'o-bacurau', [
                'awarded_at' => $loggedInAt,
            ], $notify);
        }
    }
}
