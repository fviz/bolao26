<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\Achievements\Evaluators\LoginEvaluator;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Date;

class AwardNightOwlAchievement
{
    public function __construct(
        private readonly LoginEvaluator $evaluator,
    ) {}

    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->evaluator->evaluate($event->user, Date::now());
    }
}
