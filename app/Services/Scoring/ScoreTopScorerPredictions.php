<?php

namespace App\Services\Scoring;

use App\Contracts\TournamentTopScorerResolver;
use App\Models\TopScorerPrediction;
use App\Services\Achievements\AchievementAwarder;
use App\Services\Achievements\Evaluators\TournamentEvaluator;
use App\Support\WorldCupPlayers;
use Illuminate\Support\Facades\DB;

class ScoreTopScorerPredictions
{
    private const int CorrectTopScorerPoints = 300;

    public function __construct(
        private readonly TournamentTopScorerResolver $resolver,
        private readonly AchievementAwarder $achievementAwarder,
        private readonly TournamentEvaluator $tournamentAchievements,
    ) {}

    public function score(?string $playerId = null): bool
    {
        $playerId ??= $this->resolver->resolve();

        if ($playerId === null) {
            return false;
        }

        if (! in_array($playerId, WorldCupPlayers::ids(), true)) {
            return false;
        }

        $this->achievementAwarder->beginBatch();

        DB::transaction(function () use ($playerId): void {
            TopScorerPrediction::query()
                ->with('user')
                ->each(function (TopScorerPrediction $prediction) use ($playerId): void {
                    $this->scorePrediction($prediction, $playerId);
                });
        });

        $this->achievementAwarder->flushBatches();

        return true;
    }

    private function scorePrediction(
        TopScorerPrediction $prediction,
        string $topScorerPlayerId,
    ): void {
        $newPoints = $prediction->player_id === $topScorerPlayerId
            ? self::CorrectTopScorerPoints
            : 0;

        $oldPoints = $prediction->points ?? 0;
        $delta = $newPoints - $oldPoints;

        if ($delta !== 0) {
            $prediction->user->increment('total_points', $delta);
        }

        $prediction->points = $newPoints;
        $prediction->scored_at = now();
        $prediction->save();

        $this->tournamentAchievements->evaluateTopScorer($prediction->user);
    }
}
