<?php

namespace App\Services\Achievements;

use App\Models\Prediction;
use App\Models\User;
use App\Models\UserAchievement;
use App\Support\Achievements\MatchDayGames;
use App\Support\ChampionPredictions;
use App\Support\WorldCupPlayers;

class AchievementContextEnricher
{
    /**
     * @var list<string>
     */
    private const array DAY_MEDAL_SLUGS = [
        'dupla-de-honra',
        'pe-de-rato',
    ];

    /**
     * @var list<string>
     */
    private const array TOURNAMENT_CHAMPION_SLUGS = [
        'o-profeta',
        'dono-da-copa',
    ];

    /**
     * @var list<string>
     */
    private const array TOURNAMENT_TOP_SCORER_SLUGS = [
        'olheiro-de-elite',
        'dono-da-copa',
    ];

    /**
     * @var list<string>
     */
    private const array ON_PREDICTION_SLUGS = [
        'primeiro-chute',
        'atrasado-do-enem',
        'gabaritando-a-agenda',
    ];

    public function enrich(?User $user = null): int
    {
        $updated = 0;

        UserAchievement::query()
            ->with(['achievement', 'user.championPrediction', 'user.topScorerPrediction'])
            ->when($user !== null, fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('id')
            ->each(function (UserAchievement $userAchievement) use (&$updated): void {
                $before = $userAchievement->context;
                $enriched = $this->ensureFor($userAchievement);

                if (($enriched->context ?? []) !== ($before ?? [])) {
                    $updated++;
                }
            });

        return $updated;
    }

    public function ensureFor(UserAchievement $userAchievement): UserAchievement
    {
        $userAchievement->loadMissing(['achievement', 'user.championPrediction', 'user.topScorerPrediction']);

        $slug = $userAchievement->achievement?->slug;

        if ($slug === null) {
            return $userAchievement;
        }

        $existing = $userAchievement->context ?? [];

        if ($this->hasCompleteContext($slug, $existing)) {
            return $userAchievement;
        }

        $derived = $this->deriveContext($userAchievement);

        if ($derived === null || $derived === $existing) {
            return $userAchievement;
        }

        $userAchievement->update(['context' => $derived]);

        return $userAchievement->fresh(['achievement', 'user.championPrediction', 'user.topScorerPrediction']);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function deriveContext(UserAchievement $userAchievement): ?array
    {
        $slug = $userAchievement->achievement?->slug;
        $existing = $userAchievement->context ?? [];

        if ($slug === null) {
            return null;
        }

        if ($this->hasCompleteContext($slug, $existing)) {
            return null;
        }

        if (in_array($slug, self::TOURNAMENT_CHAMPION_SLUGS, true)
            || in_array($slug, self::TOURNAMENT_TOP_SCORER_SLUGS, true)) {
            return $this->deriveTournamentContext($userAchievement, $slug, $existing);
        }

        if (in_array($slug, self::DAY_MEDAL_SLUGS, true)) {
            return $this->deriveDayContext($userAchievement, $existing);
        }

        if ($slug === 'primeiro-chute') {
            return $this->derivePrimeiroChuteContext($userAchievement, $existing);
        }

        if ($slug === 'gabaritando-a-agenda') {
            return $this->deriveGabaritandoContext($userAchievement, $existing);
        }

        if ($slug === 'atrasado-do-enem') {
            return $this->deriveOnPredictionContext($userAchievement, $existing);
        }

        return $this->deriveGameScoredContext($userAchievement, $existing);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function hasCompleteContext(string $slug, array $context): bool
    {
        if (in_array($slug, self::DAY_MEDAL_SLUGS, true)) {
            return isset($context['match_day']);
        }

        if ($slug === 'o-profeta') {
            return isset($context['fifa_team_id']);
        }

        if ($slug === 'olheiro-de-elite') {
            return isset($context['player_id']);
        }

        if ($slug === 'dono-da-copa') {
            return isset($context['fifa_team_id'], $context['player_id']);
        }

        return isset($context['game_id']);
    }

    /**
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>|null
     */
    private function deriveTournamentContext(UserAchievement $userAchievement, string $slug, array $existing): ?array
    {
        $user = $userAchievement->user;

        if ($user === null) {
            return null;
        }

        $context = $existing;

        if (in_array($slug, self::TOURNAMENT_CHAMPION_SLUGS, true)) {
            $championPrediction = $user->championPrediction;

            if ($championPrediction !== null) {
                $team = collect(ChampionPredictions::availableTeams())
                    ->firstWhere('fifaTeamId', $championPrediction->fifa_team_id);

                $context['fifa_team_id'] = $championPrediction->fifa_team_id;
                $context['team_name'] = $team['name'] ?? $championPrediction->fifa_team_id;
                $context['team_abbr'] = $team['abbr'] ?? null;
            }
        }

        if (in_array($slug, self::TOURNAMENT_TOP_SCORER_SLUGS, true)) {
            $topScorerPrediction = $user->topScorerPrediction;

            if ($topScorerPrediction !== null) {
                $player = WorldCupPlayers::find($topScorerPrediction->player_id);

                $context['player_id'] = $topScorerPrediction->player_id;
                $context['player_name'] = $player['name'] ?? $topScorerPrediction->player_id;
            }
        }

        return $context === [] ? null : $context;
    }

    /**
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>|null
     */
    private function deriveDayContext(UserAchievement $userAchievement, array $existing): ?array
    {
        $matchDay = $userAchievement->awarded_at?->toDateString();

        if ($matchDay === null) {
            return null;
        }

        $game = MatchDayGames::forDate($matchDay)->last();

        return array_merge($existing, [
            'match_day' => $matchDay,
            'game_id' => $game?->id ?? ($existing['game_id'] ?? null),
        ]);
    }

    /**
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>|null
     */
    private function derivePrimeiroChuteContext(UserAchievement $userAchievement, array $existing): ?array
    {
        $firstPrediction = Prediction::query()
            ->where('user_id', $userAchievement->user_id)
            ->oldest('created_at')
            ->first();

        if ($firstPrediction === null) {
            return null;
        }

        return array_merge($existing, [
            'game_id' => $firstPrediction->game_id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>|null
     */
    private function deriveGabaritandoContext(UserAchievement $userAchievement, array $existing): ?array
    {
        $latestPrediction = Prediction::query()
            ->where('user_id', $userAchievement->user_id)
            ->whereHas('game', fn ($query) => $query->whereNotNull('id_group'))
            ->latest('created_at')
            ->first();

        if ($latestPrediction === null) {
            return null;
        }

        return array_merge($existing, [
            'game_id' => $latestPrediction->game_id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>|null
     */
    private function deriveGameScoredContext(UserAchievement $userAchievement, array $existing): ?array
    {
        if ($userAchievement->awarded_at === null) {
            return null;
        }

        $prediction = Prediction::query()
            ->where('user_id', $userAchievement->user_id)
            ->whereHas('game', function ($query) use ($userAchievement): void {
                $query->where('scored_at', $userAchievement->awarded_at);
            })
            ->first();

        if ($prediction === null) {
            $prediction = Prediction::query()
                ->where('user_id', $userAchievement->user_id)
                ->whereHas('game', function ($query) use ($userAchievement): void {
                    $query->whereBetween('scored_at', [
                        $userAchievement->awarded_at->copy()->subMinute(),
                        $userAchievement->awarded_at->copy()->addMinute(),
                    ]);
                })
                ->first();
        }

        if ($prediction === null) {
            $prediction = Prediction::query()
                ->where('user_id', $userAchievement->user_id)
                ->whereHas('game', function ($query) use ($userAchievement): void {
                    $query->where('scheduled_at', $userAchievement->awarded_at);
                })
                ->first();
        }

        if ($prediction === null) {
            $prediction = Prediction::query()
                ->where('user_id', $userAchievement->user_id)
                ->whereHas('game', function ($query) use ($userAchievement): void {
                    $query->whereBetween('scheduled_at', [
                        $userAchievement->awarded_at->copy()->subMinute(),
                        $userAchievement->awarded_at->copy()->addMinute(),
                    ]);
                })
                ->first();
        }

        if ($prediction === null) {
            return null;
        }

        return array_merge($existing, [
            'game_id' => $prediction->game_id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>|null
     */
    private function deriveOnPredictionContext(UserAchievement $userAchievement, array $existing): ?array
    {
        if ($userAchievement->awarded_at === null) {
            return null;
        }

        $awardedAt = $userAchievement->awarded_at;

        $prediction = Prediction::query()
            ->where('user_id', $userAchievement->user_id)
            ->whereBetween('created_at', [
                $awardedAt->copy()->subMinute(),
                $awardedAt->copy()->addMinute(),
            ])
            ->latest('created_at')
            ->first();

        if ($prediction === null) {
            $prediction = Prediction::query()
                ->where('user_id', $userAchievement->user_id)
                ->get()
                ->sortBy(fn (Prediction $candidate) => abs($candidate->created_at->diffInSeconds($awardedAt)))
                ->first();
        }

        if ($prediction === null) {
            return null;
        }

        return array_merge($existing, [
            'game_id' => $prediction->game_id,
        ]);
    }
}
