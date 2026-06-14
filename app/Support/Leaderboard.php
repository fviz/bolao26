<?php

namespace App\Support;

use App\Http\Resources\FeaturedAchievementResource;
use App\Models\User;
use Illuminate\Support\Collection;

final class Leaderboard
{
    /**
     * @return Collection<int, array{id: int, name: string, avatar: ?string, totalPoints: int, rank: int, isCurrentUser: bool}>
     */
    public static function rankedEntries(?User $currentUser = null): Collection
    {
        $users = User::query()
            ->with('featuredAchievement')
            ->orderByDesc('total_points')
            ->orderBy('name')
            ->get(['id', 'name', 'avatar_path', 'total_points', 'featured_achievement_id']);

        $rank = 1;

        return $users->values()->map(function (User $user, int $index) use ($users, &$rank, $currentUser): array {
            if ($index > 0 && $user->total_points < $users[$index - 1]->total_points) {
                $rank = $index + 1;
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'totalPoints' => $user->total_points,
                'rank' => $rank,
                'isCurrentUser' => $currentUser !== null && $user->is($currentUser),
                'featuredAchievement' => FeaturedAchievementResource::forUser($user),
            ];
        });
    }

    /**
     * @return Collection<int, array{id: int, name: string, avatar: ?string, diamondCount: int, goldCount: int, silverCount: int, bronzeCount: int, lixoHumanoCount: int, rank: int, isCurrentUser: bool}>
     */
    public static function medalRankedEntries(?User $currentUser = null): Collection
    {
        $users = User::query()
            ->with('featuredAchievement')
            ->select(['users.id', 'users.name', 'users.avatar_path', 'users.featured_achievement_id'])
            ->selectRaw("COALESCE(SUM(CASE WHEN achievements.tier = 'diamond' THEN 1 END), 0) as diamond_count")
            ->selectRaw("COALESCE(SUM(CASE WHEN achievements.tier = 'gold' THEN 1 END), 0) as gold_count")
            ->selectRaw("COALESCE(SUM(CASE WHEN achievements.tier = 'silver' THEN 1 END), 0) as silver_count")
            ->selectRaw("COALESCE(SUM(CASE WHEN achievements.tier = 'bronze' THEN 1 END), 0) as bronze_count")
            ->selectRaw("COALESCE(SUM(CASE WHEN achievements.tier = 'lixo_humano' THEN 1 END), 0) as lixo_humano_count")
            ->leftJoin('user_achievements', 'user_achievements.user_id', '=', 'users.id')
            ->leftJoin('achievements', 'achievements.id', '=', 'user_achievements.achievement_id')
            ->groupBy('users.id', 'users.name', 'users.avatar_path', 'users.featured_achievement_id')
            ->orderByDesc('diamond_count')
            ->orderByDesc('gold_count')
            ->orderByDesc('silver_count')
            ->orderByDesc('bronze_count')
            ->orderBy('users.name')
            ->get();

        $rank = 1;

        return $users->values()->map(function (User $user, int $index) use ($users, &$rank, $currentUser): array {
            if ($index > 0 && self::hasLowerMedalCount($user, $users[$index - 1])) {
                $rank = $index + 1;
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'diamondCount' => (int) $user->diamond_count,
                'goldCount' => (int) $user->gold_count,
                'silverCount' => (int) $user->silver_count,
                'bronzeCount' => (int) $user->bronze_count,
                'lixoHumanoCount' => (int) $user->lixo_humano_count,
                'rank' => $rank,
                'isCurrentUser' => $currentUser !== null && $user->is($currentUser),
                'featuredAchievement' => FeaturedAchievementResource::forUser($user),
            ];
        });
    }

    private static function hasLowerMedalCount(User $current, User $previous): bool
    {
        foreach (['diamond_count', 'gold_count', 'silver_count', 'bronze_count'] as $tier) {
            if ((int) $current->{$tier} < (int) $previous->{$tier}) {
                return true;
            }

            if ((int) $current->{$tier} > (int) $previous->{$tier}) {
                return false;
            }
        }

        return false;
    }

    /**
     * @param  Collection<int, array{id: int, name: string, avatar: ?string, totalPoints: int, rank: int, isCurrentUser: bool}>  $entries
     * @return Collection<int, array{id: int, name: string, avatar: ?string, totalPoints: int, rank: int, isCurrentUser: bool}>
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
