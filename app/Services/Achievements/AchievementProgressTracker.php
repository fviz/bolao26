<?php

namespace App\Services\Achievements;

use App\Models\User;
use App\Models\UserAchievementProgress;
use App\Support\Achievements\AchievementCatalog;

class AchievementProgressTracker
{
    public function get(User $user, string $slug): int
    {
        $achievementId = AchievementCatalog::id($slug);

        if ($achievementId === null) {
            return 0;
        }

        return UserAchievementProgress::query()
            ->where('user_id', $user->id)
            ->where('achievement_id', $achievementId)
            ->value('current_value') ?? 0;
    }

    public function set(User $user, string $slug, int $value): void
    {
        $achievementId = AchievementCatalog::id($slug);

        if ($achievementId === null) {
            return;
        }

        UserAchievementProgress::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'achievement_id' => $achievementId,
            ],
            [
                'current_value' => max(0, $value),
            ],
        );
    }

    public function increment(User $user, string $slug, int $by = 1): int
    {
        $current = $this->get($user, $slug);
        $new = $current + $by;
        $this->set($user, $slug, $new);

        return $new;
    }
}
