<?php

namespace App\Http\Controllers;

use App\Http\Resources\AchievementResource;
use App\Http\Resources\UserProfileResource;
use App\Models\Achievement;
use App\Models\User;
use App\Support\Achievements\UserAchievementData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserAchievementController extends Controller
{
    public function index(Request $request, User $user): Response
    {
        $sort = in_array($request->query('sort'), ['catalog', 'name', 'awarded'], true)
            ? $request->query('sort')
            : 'catalog';

        $achievements = UserAchievementData::forUser($user, $sort)
            ->map(fn (array $item) => AchievementResource::fromItem($item)->resolve());

        return Inertia::render('achievements/Index', [
            'profile' => UserProfileResource::make($user),
            'achievements' => $achievements,
            'achievementSummary' => UserAchievementData::summaryForUser($user),
            'sort' => $sort,
        ]);
    }

    public function show(Request $request, User $user, Achievement $achievement): Response
    {
        $item = UserAchievementData::forUser($user)
            ->first(fn (array $data) => $data['achievement']->is($achievement));

        return Inertia::render('achievements/Show', [
            'profile' => UserProfileResource::make($user),
            'achievement' => AchievementResource::fromItem($item ?? [
                'achievement' => $achievement,
                'earned' => false,
                'awardedAt' => null,
                'progressCurrent' => null,
                'progressTarget' => $achievement->progress_target,
            ])->resolve(),
            'achievementEarnedPercentage' => UserAchievementData::earnedPercentageForAchievement($achievement),
        ]);
    }
}
