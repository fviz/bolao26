<?php

namespace App\Services\Scoring;

use App\Contracts\TournamentTopScorerResolver;
use App\Models\TopScorerPrediction;
use App\Support\WorldCupPlayers;
use Illuminate\Support\Facades\DB;

class ScoreTopScorerPredictions
{
    private const int CorrectTopScorerPoints = 300;

    public function __construct(
        private readonly TournamentTopScorerResolver $resolver,
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

        DB::transaction(function () use ($playerId): void {
            TopScorerPrediction::query()
                ->with('user')
                ->each(function (TopScorerPrediction $prediction) use ($playerId): void {
                    $this->scorePrediction($prediction, $playerId);
                });
        });

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
    }
}
