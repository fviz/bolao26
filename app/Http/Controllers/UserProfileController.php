<?php

namespace App\Http\Controllers;

use App\Http\Resources\AchievementResource;
use App\Http\Resources\ProfileGameResource;
use App\Http\Resources\UserProfileResource;
use App\Models\Game;
use App\Models\User;
use App\Support\Achievements\UserAchievementData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserProfileController extends Controller
{
    public function show(Request $request, User $user): Response
    {
        $user->loadMissing('featuredAchievement');
        $featuredSlug = $user->featuredAchievement?->slug;

        $finishedGames = Game::query()
            ->finished()
            ->whereHas('predictions', fn ($query) => $query->where('user_id', $user->id))
            ->with(['predictions' => fn ($query) => $query->where('user_id', $user->id)])
            ->orderByDesc('scheduled_at')
            ->paginate(20);

        return Inertia::render('users/Show', [
            'profile' => UserProfileResource::make($user),
            'finishedGames' => Inertia::scroll(
                ProfileGameResource::collection($finishedGames),
            ),
            'earnedAchievements' => UserAchievementData::earnedForUser($user)
                ->map(fn (array $item) => AchievementResource::fromItem(
                    $item,
                    isFeatured: $item['achievement']->slug === $featuredSlug,
                )->resolve()),
            'achievementSummary' => UserAchievementData::summaryForUser($user),
        ]);
    }
}
