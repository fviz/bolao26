<?php

namespace App\Services\Achievements;

use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use App\Services\Achievements\Evaluators\DayCompleteEvaluator;
use App\Services\Achievements\Evaluators\GameScoredEvaluator;
use App\Services\Achievements\Evaluators\GameSocialEvaluator;
use App\Services\Achievements\Evaluators\PredictionSavedEvaluator;
use App\Services\Achievements\Evaluators\TournamentEvaluator;

class AchievementBackfiller
{
    public function __construct(
        private readonly AchievementAwarder $awarder,
        private readonly GameScoredEvaluator $gameScoredEvaluator,
        private readonly GameSocialEvaluator $gameSocialEvaluator,
        private readonly DayCompleteEvaluator $dayCompleteEvaluator,
        private readonly PredictionSavedEvaluator $predictionSavedEvaluator,
        private readonly TournamentEvaluator $tournamentEvaluator,
    ) {}

    public function backfill(?User $user = null, bool $notify = false): void
    {
        $this->awarder->beginBatch();

        $games = Game::query()
            ->finished()
            ->whereNotNull('scored_at')
            ->orderBy('scheduled_at')
            ->get();

        foreach ($games as $game) {
            $game->predictions()
                ->with('user')
                ->when($user !== null, fn ($query) => $query->where('user_id', $user->id))
                ->whereNotNull('points')
                ->each(function (Prediction $prediction) use ($game): void {
                    if ($prediction->user === null) {
                        return;
                    }

                    $this->gameScoredEvaluator->evaluate(
                        $prediction->user,
                        $game,
                        $prediction,
                        $prediction->points ?? 0,
                        $prediction->points ?? 0,
                        notify: false,
                    );
                });

            $this->gameSocialEvaluator->evaluate($game, notify: false);
            $this->dayCompleteEvaluator->evaluateForGame($game, notify: false);
        }

        $users = $user !== null
            ? User::query()->whereKey($user->id)->get()
            : User::query()->get();

        foreach ($users as $subject) {
            $subject->predictions()
                ->with('game')
                ->orderBy('created_at')
                ->each(function (Prediction $prediction) use ($subject): void {
                    if ($prediction->game === null) {
                        return;
                    }

                    $this->predictionSavedEvaluator->evaluate(
                        $subject,
                        $prediction->game,
                        $prediction,
                        notify: false,
                        placedAt: $prediction->created_at,
                    );
                });

            $this->tournamentEvaluator->evaluateChampion($subject, notify: false);
            $this->tournamentEvaluator->evaluateTopScorer($subject, notify: false);
        }

        $this->awarder->flushBatches($notify);
    }
}
