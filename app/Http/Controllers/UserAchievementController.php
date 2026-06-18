<?php

namespace App\Http\Controllers;

use App\Http\Resources\AchievementResource;
use App\Http\Resources\UserProfileResource;
use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use App\Services\Achievements\AchievementContextEnricher;
use App\Support\Achievements\AchievementAwardContextResolver;
use App\Support\Achievements\UserAchievementData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserAchievementController extends Controller
{
    public function index(Request $request, User $user): Response
    {
        $user->loadMissing('featuredAchievement');
        $featuredSlug = $user->featuredAchievement?->slug;

        $sort = in_array($request->query('sort'), ['catalog', 'name', 'awarded', 'tier_asc', 'tier_desc'], true)
            ? $request->query('sort')
            : 'catalog';

        $achievements = UserAchievementData::forUser($user, $sort)
            ->map(fn (array $item) => AchievementResource::fromItem(
                $item,
                isFeatured: $item['achievement']->slug === $featuredSlug,
            )->resolve());

        return Inertia::render('achievements/Index', [
            'profile' => UserProfileResource::make($user),
            'achievements' => $achievements,
            'achievementSummary' => UserAchievementData::summaryForUser($user),
            'sort' => $sort,
        ]);
    }

    public function show(Request $request, User $user, Achievement $achievement, AchievementContextEnricher $contextEnricher): Response
    {
        $user->loadMissing('featuredAchievement');

        $item = UserAchievementData::forUser($user)
            ->first(fn (array $data) => $data['achievement']->is($achievement));

        $isFeatured = $user->featured_achievement_id === $achievement->id;

        $awardContext = null;

        if ($item !== null && $item['earned']) {
            $userAchievement = UserAchievement::query()
                ->with(['achievement', 'user.championPrediction', 'user.topScorerPrediction'])
                ->where('user_id', $user->id)
                ->where('achievement_id', $achievement->id)
                ->first();

            if ($userAchievement !== null) {
                $userAchievement = $contextEnricher->ensureFor($userAchievement);
                $awardContext = AchievementAwardContextResolver::resolve($userAchievement, $achievement);
            }
        }

        return Inertia::render('achievements/Show', [
            'profile' => UserProfileResource::make($user),
            'achievement' => AchievementResource::fromItem($item ?? [
                'achievement' => $achievement,
                'earned' => false,
                'awardedAt' => null,
                'progressCurrent' => null,
                'progressTarget' => $achievement->progress_target,
            ], isFeatured: $isFeatured, awardContext: $awardContext)->resolve(),
            'achievementEarnedPercentage' => UserAchievementData::earnedPercentageForAchievement($achievement),
        ]);
    }
}
