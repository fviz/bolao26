<?php

namespace App\Services\Achievements\Evaluators;

use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use App\Services\Achievements\AchievementAwarder;
use App\Services\Achievements\AchievementProgressTracker;
use App\Services\Achievements\Support\PredictionTiming;
use Carbon\CarbonInterface;

class PredictionSavedEvaluator
{
    public function __construct(
        private readonly AchievementAwarder $awarder,
        private readonly AchievementProgressTracker $progress,
    ) {}

    public function evaluate(
        User $user,
        Game $game,
        ?Prediction $prediction = null,
        bool $notify = true,
        ?CarbonInterface $placedAt = null,
    ): void {
        $prediction ??= $user->predictions()->where('game_id', $game->id)->first();

        $placedAt ??= now();
        $awardedAt = $prediction?->created_at ?? $placedAt;

        if (! $this->awarder->has($user, 'primeiro-chute')) {
            $firstPrediction = $user->predictions()->oldest('created_at')->first();

            if ($firstPrediction !== null) {
                $this->awarder->award($user, 'primeiro-chute', [
                    'game_id' => $firstPrediction->game_id,
                    'awarded_at' => $firstPrediction->created_at,
                ], $notify);
            }
        }

        if (PredictionTiming::isLateBet($game, $placedAt)) {
            $this->awarder->award($user, 'atrasado-do-enem', [
                'game_id' => $game->id,
                'awarded_at' => $placedAt,
            ], $notify);
        }

        $this->evaluateGroupStageCompletion($user, $game, $awardedAt, $notify);
    }

    private function evaluateGroupStageCompletion(User $user, Game $game, $awardedAt, bool $notify): void
    {
        $totalGroupStageGames = Game::query()
            ->whereNotNull('id_group')
            ->count();

        if ($totalGroupStageGames === 0) {
            return;
        }

        $userGroupStagePredictions = $user->predictions()
            ->whereHas('game', fn ($query) => $query->whereNotNull('id_group'))
            ->get();

        $userGroupStageCount = $userGroupStagePredictions->count();

        $this->progress->set($user, 'gabaritando-a-agenda', $userGroupStageCount);

        if ($userGroupStageCount >= $totalGroupStageGames) {
            $latestPredictionAt = $userGroupStagePredictions->max('created_at') ?? $awardedAt;

            $this->awarder->award($user, 'gabaritando-a-agenda', [
                'game_id' => $game->id,
                'awarded_at' => $latestPredictionAt,
            ], $notify);
        }
    }
}
