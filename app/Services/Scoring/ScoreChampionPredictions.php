<?php

namespace App\Services\Scoring;

use App\Models\ChampionPrediction;
use App\Models\Game;
use App\Services\Achievements\AchievementAwarder;
use App\Services\Achievements\Evaluators\TournamentEvaluator;
use Illuminate\Support\Facades\DB;

class ScoreChampionPredictions
{
    private const int CorrectChampionPoints = 300;

    public function __construct(
        private readonly AchievementAwarder $achievementAwarder,
        private readonly TournamentEvaluator $tournamentAchievements,
    ) {}

    public function scoreForFinal(Game $game): bool
    {
        if (! $game->isTournamentFinal() || ! $game->isReadyForScoring()) {
            return false;
        }

        $championTeamId = $game->winningFifaTeamId();

        if ($championTeamId === null) {
            return false;
        }

        $this->achievementAwarder->beginBatch();

        DB::transaction(function () use ($championTeamId): void {
            ChampionPrediction::query()
                ->with('user')
                ->each(function (ChampionPrediction $championPrediction) use ($championTeamId): void {
                    $this->scoreChampionPrediction($championPrediction, $championTeamId);
                });
        });

        $this->achievementAwarder->flushBatches();

        return true;
    }

    private function scoreChampionPrediction(
        ChampionPrediction $championPrediction,
        string $championTeamId,
    ): void {
        $newPoints = $championPrediction->fifa_team_id === $championTeamId
            ? self::CorrectChampionPoints
            : 0;

        $oldPoints = $championPrediction->points ?? 0;
        $delta = $newPoints - $oldPoints;

        if ($delta !== 0) {
            $championPrediction->user->increment('total_points', $delta);
        }

        $championPrediction->points = $newPoints;
        $championPrediction->scored_at = now();
        $championPrediction->save();

        $this->tournamentAchievements->evaluateChampion($championPrediction->user);
    }
}
