<?php

namespace App\Services\Achievements\Evaluators;

use App\Models\User;
use App\Services\Achievements\AchievementAwarder;
use App\Support\ChampionPredictions;
use App\Support\WorldCupPlayers;

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
            $this->awarder->award($user, 'o-profeta', array_merge(
                $this->championContext($championPrediction->fifa_team_id),
                ['awarded_at' => $championPrediction->scored_at ?? now()],
            ), $notify);
        }

        $this->evaluateDonoDaCopa($user, $notify);
    }

    public function evaluateTopScorer(User $user, bool $notify = true): void
    {
        $topScorerPrediction = $user->topScorerPrediction;

        if ($topScorerPrediction !== null && ($topScorerPrediction->points ?? 0) === self::CorrectPoints) {
            $this->awarder->award($user, 'olheiro-de-elite', array_merge(
                $this->topScorerContext($topScorerPrediction->player_id),
                ['awarded_at' => $topScorerPrediction->scored_at ?? now()],
            ), $notify);
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

            $this->awarder->award($user, 'dono-da-copa', array_merge(
                $this->championContext($user->championPrediction->fifa_team_id),
                $this->topScorerContext($user->topScorerPrediction->player_id),
                ['awarded_at' => $awardedAt],
            ), $notify);
        }
    }

    /**
     * @return array{fifa_team_id: string, team_name: string, team_abbr: string|null}
     */
    private function championContext(string $fifaTeamId): array
    {
        $team = collect(ChampionPredictions::availableTeams())
            ->firstWhere('fifaTeamId', $fifaTeamId);

        return [
            'fifa_team_id' => $fifaTeamId,
            'team_name' => $team['name'] ?? $fifaTeamId,
            'team_abbr' => $team['abbr'] ?? null,
        ];
    }

    /**
     * @return array{player_id: string, player_name: string}
     */
    private function topScorerContext(string $playerId): array
    {
        $player = WorldCupPlayers::find($playerId);

        return [
            'player_id' => $playerId,
            'player_name' => $player['name'] ?? $playerId,
        ];
    }
}
