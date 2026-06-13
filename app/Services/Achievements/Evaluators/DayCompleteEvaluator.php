<?php

namespace App\Services\Achievements\Evaluators;

use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use App\Services\Achievements\AchievementAwarder;
use Illuminate\Support\Collection;

class DayCompleteEvaluator
{
    public function __construct(
        private readonly AchievementAwarder $awarder,
    ) {}

    public function evaluateForGame(Game $game, bool $notify = true): void
    {
        $date = $this->gameDate($game);

        if ($date === null) {
            return;
        }

        if (! $this->isDayComplete($date)) {
            return;
        }

        $gamesOnDay = Game::query()
            ->whereDate('local_scheduled_at', $date)
            ->get();

        if ($gamesOnDay->isEmpty()) {
            $gamesOnDay = Game::query()
                ->whereDate('scheduled_at', $date)
                ->get();
        }

        if ($gamesOnDay->isEmpty()) {
            return;
        }

        $gameIds = $gamesOnDay->pluck('id');
        $latestScoredAt = $gamesOnDay
            ->map(fn (Game $dayGame) => $dayGame->scored_at)
            ->filter()
            ->max();

        $awardedAt = $latestScoredAt
            ?? $game->scored_at
            ?? $game->scheduled_at
            ?? now();

        $context = ['awarded_at' => $awardedAt];

        $userIds = Prediction::query()
            ->whereIn('game_id', $gameIds)
            ->distinct()
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            $user = User::query()->find($userId);

            if ($user === null) {
                continue;
            }

            $this->evaluateDuplaDeHonra($user, $gameIds, $context, $notify);
            $this->evaluatePeDeRato($user, $gameIds, $context, $notify);
        }
    }

    private function gameDate(Game $game): ?string
    {
        $date = $game->local_scheduled_at ?? $game->scheduled_at;

        return $date?->toDateString();
    }

    private function isDayComplete(string $date): bool
    {
        $gamesOnDay = Game::query()
            ->whereDate('local_scheduled_at', $date)
            ->get();

        if ($gamesOnDay->isEmpty()) {
            $gamesOnDay = Game::query()
                ->whereDate('scheduled_at', $date)
                ->get();
        }

        if ($gamesOnDay->isEmpty()) {
            return false;
        }

        foreach ($gamesOnDay as $game) {
            if (! $game->is_final || $game->scored_at === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  Collection<int, int>  $gameIds
     * @param  array<string, mixed>  $context
     */
    private function evaluateDuplaDeHonra(User $user, $gameIds, array $context, bool $notify): void
    {
        $exactCount = Prediction::query()
            ->where('user_id', $user->id)
            ->whereIn('game_id', $gameIds)
            ->where('points', 200)
            ->count();

        if ($exactCount >= 2) {
            $this->awarder->award($user, 'dupla-de-honra', $context, $notify);
        }
    }

    /**
     * @param  Collection<int, int>  $gameIds
     * @param  array<string, mixed>  $context
     */
    private function evaluatePeDeRato(User $user, $gameIds, array $context, bool $notify): void
    {
        $userPredictions = Prediction::query()
            ->where('user_id', $user->id)
            ->whereIn('game_id', $gameIds)
            ->get();

        if ($userPredictions->count() !== $gameIds->count()) {
            return;
        }

        $allZero = $userPredictions->every(fn (Prediction $p) => ($p->points ?? 0) === 0);

        if ($allZero) {
            $this->awarder->award($user, 'pe-de-rato', $context, $notify);
        }
    }
}
