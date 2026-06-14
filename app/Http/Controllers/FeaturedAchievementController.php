<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateFeaturedAchievementRequest;
use App\Models\Achievement;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class FeaturedAchievementController extends Controller
{
    public function update(UpdateFeaturedAchievementRequest $request, User $user): RedirectResponse
    {
        $slug = $request->validated('achievementSlug');

        $featuredAchievementId = $slug === null
            ? null
            : Achievement::query()->where('slug', $slug)->value('id');

        $user->featured_achievement_id = $featuredAchievementId;
        $user->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $slug === null
                ? __('Medalha em destaque removida.')
                : __('Medalha em destaque atualizada.'),
        ]);

        return back();
    }
}
