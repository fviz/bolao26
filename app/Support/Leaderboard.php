<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;

final class Leaderboard
{
    /**
     * @return Collection<int, array{id: int, name: string, totalPoints: int, rank: int, isCurrentUser: bool}>
     */
    public static function rankedEntries(?User $currentUser = null): Collection
    {
        $users = User::query()
            ->orderByDesc('total_points')
            ->orderBy('name')
            ->get(['id', 'name', 'total_points']);

        $rank = 1;

        return $users->values()->map(function (User $user, int $index) use ($users, &$rank, $currentUser): array {
            if ($index > 0 && $user->total_points < $users[$index - 1]->total_points) {
                $rank = $index + 1;
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'totalPoints' => $user->total_points,
                'rank' => $rank,
                'isCurrentUser' => $currentUser !== null && $user->is($currentUser),
            ];
        });
    }

    /**
     * @param  Collection<int, array{id: int, name: string, totalPoints: int, rank: int, isCurrentUser: bool}>  $entries
     * @return Collection<int, array{id: int, name: string, totalPoints: int, rank: int, isCurrentUser: bool}>
     */
    public static function windowForUser(Collection $entries, int $userId, int $size = 5): Collection
    {
        if ($entries->isEmpty()) {
            return $entries;
        }

        $index = $entries->search(fn (array $entry): bool => $entry['id'] === $userId);

        if ($index === false) {
            return $entries->take($size)->values();
        }

        if ($entries->count() <= $size) {
            return $entries->values();
        }

        $half = intdiv($size - 1, 2);
        $start = max(0, $index - $half);
        $end = $start + $size - 1;

        if ($end >= $entries->count()) {
            $start = max(0, $entries->count() - $size);
        }

        return $entries->slice($start, $size)->values();
    }
}
