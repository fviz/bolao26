<?php

namespace App\Support\Achievements;

use App\Models\Achievement;
use App\Models\Game;
use App\Models\User;
use App\Models\UserAchievement;
use App\Models\UserAchievementProgress;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class UserAchievementData
{
    /**
     * @return Collection<int, array{achievement: Achievement, earned: bool, awardedAt: ?Carbon, progressCurrent: ?int, progressTarget: ?int}>
     */
    public static function forUser(User $user, string $sort = 'catalog'): Collection
    {
        $items = self::buildForUser($user);

        return self::sortItems($items, $sort);
    }

    /**
     * @return Collection<int, array{achievement: Achievement, earned: bool, awardedAt: ?Carbon, progressCurrent: ?int, progressTarget: ?int}>
     */
    private static function buildForUser(User $user): Collection
    {
        $achievements = Achievement::query()->orderBy('sort_order')->get();

        $earned = UserAchievement::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('achievement_id');

        $progress = UserAchievementProgress::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('achievement_id');

        $groupStageGameCount = Game::query()->whereNotNull('id_group')->count();

        return $achievements->map(function (Achievement $achievement) use ($earned, $progress, $groupStageGameCount, $user) {
            $userAchievement = $earned->get($achievement->id);
            $userProgress = $progress->get($achievement->id);

            $progressTarget = $achievement->progress_target;
            $progressCurrent = $userProgress?->current_value;

            if ($achievement->slug === 'gabaritando-a-agenda' && $groupStageGameCount > 0) {
                $progressTarget = $groupStageGameCount;
                $progressCurrent = $userProgress?->current_value ?? $user->predictions()
                    ->whereHas('game', fn ($query) => $query->whereNotNull('id_group'))
                    ->count();
            }

            return [
                'achievement' => $achievement,
                'earned' => $userAchievement !== null,
                'awardedAt' => $userAchievement?->awarded_at,
                'progressCurrent' => $progressTarget !== null ? ($progressCurrent ?? 0) : null,
                'progressTarget' => $progressTarget,
            ];
        });
    }

    /**
     * @param  Collection<int, array{achievement: Achievement, earned: bool, awardedAt: ?Carbon, progressCurrent: ?int, progressTarget: ?int}>  $items
     * @return Collection<int, array{achievement: Achievement, earned: bool, awardedAt: ?Carbon, progressCurrent: ?int, progressTarget: ?int}>
     */
    private static function sortItems(Collection $items, string $sort): Collection
    {
        if ($sort === 'name') {
            return $items
                ->sortBy(fn (array $item) => mb_strtolower($item['achievement']->name))
                ->values();
        }

        if ($sort === 'awarded') {
            return $items
                ->sort(function (array $first, array $second): int {
                    if ($first['earned'] !== $second['earned']) {
                        return $first['earned'] ? -1 : 1;
                    }

                    if ($first['earned']) {
                        return ($second['awardedAt']?->timestamp ?? 0)
                            <=> ($first['awardedAt']?->timestamp ?? 0);
                    }

                    return $first['achievement']->sort_order <=> $second['achievement']->sort_order;
                })
                ->values();
        }

        if ($sort === 'tier_asc') {
            return $items
                ->sort(function (array $first, array $second): int {
                    $tierComparison = $first['achievement']->tier->rank()
                        <=> $second['achievement']->tier->rank();

                    if ($tierComparison !== 0) {
                        return $tierComparison;
                    }

                    return $first['achievement']->sort_order <=> $second['achievement']->sort_order;
                })
                ->values();
        }

        if ($sort === 'tier_desc') {
            return $items
                ->sort(function (array $first, array $second): int {
                    $tierComparison = $second['achievement']->tier->rank()
                        <=> $first['achievement']->tier->rank();

                    if ($tierComparison !== 0) {
                        return $tierComparison;
                    }

                    return $first['achievement']->sort_order <=> $second['achievement']->sort_order;
                })
                ->values();
        }

        return $items->values();
    }

    public static function totalCount(): int
    {
        return Achievement::query()->count();
    }

    public static function earnedCountForUser(User $user): int
    {
        return self::buildForUser($user)
            ->filter(fn (array $item) => $item['earned'])
            ->count();
    }

    public static function earnedPercentageForAchievement(Achievement $achievement): int
    {
        $totalUsers = User::query()->count();

        if ($totalUsers === 0) {
            return 0;
        }

        $earnedCount = UserAchievement::query()
            ->where('achievement_id', $achievement->id)
            ->count();

        return (int) round(($earnedCount / $totalUsers) * 100);
    }

    /**
     * @return array{earned: int, total: int}
     */
    public static function summaryForUser(User $user): array
    {
        return [
            'earned' => self::earnedCountForUser($user),
            'total' => self::totalCount(),
        ];
    }

    /**
     * @return Collection<int, array{achievement: Achievement, earned: bool, awardedAt: ?Carbon, progressCurrent: ?int, progressTarget: ?int}>
     */
    public static function earnedForUser(User $user, int $limit = 12): Collection
    {
        return self::forUser($user)
            ->filter(fn (array $item) => $item['earned'])
            ->sortByDesc(fn (array $item) => $item['awardedAt']?->timestamp ?? 0)
            ->take($limit)
            ->values();
    }
}
