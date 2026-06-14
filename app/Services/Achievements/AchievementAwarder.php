<?php

namespace App\Services\Achievements;

use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use App\Notifications\AchievementEarned;
use App\Support\Achievements\AchievementCatalog;
use App\Support\NotificationDispatcher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AchievementAwarder
{
    private bool $batching = false;

    private ?string $batchToken = null;

    /** @var array<int, list<Achievement>> */
    private array $pendingAchievements = [];

    public function __construct(
        private readonly NotificationDispatcher $notifications,
    ) {}

    public function beginBatch(): void
    {
        $this->batching = true;
        $this->batchToken = (string) Str::uuid();
        $this->pendingAchievements = [];
    }

    public function flushBatches(bool $notify = true): void
    {
        if ($notify) {
            foreach ($this->pendingAchievements as $userId => $achievements) {
                if ($achievements === []) {
                    continue;
                }

                $user = User::query()->find($userId);

                if ($user === null) {
                    continue;
                }

                $this->sendNotification($user, $achievements, $this->batchToken);
            }
        }

        $this->batching = false;
        $this->batchToken = null;
        $this->pendingAchievements = [];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function award(User $user, string $slug, array $context = [], bool $notify = true): bool
    {
        $achievement = AchievementCatalog::find($slug);

        if ($achievement === null) {
            return false;
        }

        $awardedAt = isset($context['awarded_at'])
            ? Carbon::parse($context['awarded_at'])
            : now();

        $storedContext = $context;
        unset($storedContext['awarded_at']);

        $userAchievement = UserAchievement::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
            ],
            [
                'awarded_at' => $awardedAt,
                'context' => $storedContext === [] ? null : $storedContext,
            ],
        );

        if (! $userAchievement->wasRecentlyCreated) {
            return false;
        }

        if ($slug === 'traidor-da-patria') {
            $user->featured_achievement_id = $achievement->id;
            $user->save();
        }

        if (! $notify) {
            return true;
        }

        if ($this->batching) {
            $this->pendingAchievements[$user->id] ??= [];
            $this->pendingAchievements[$user->id][] = $achievement;

            return true;
        }

        $this->sendNotification($user, [$achievement], "achievement:{$user->id}:{$slug}");

        return true;
    }

    public function has(User $user, string $slug): bool
    {
        $achievementId = AchievementCatalog::id($slug);

        if ($achievementId === null) {
            return false;
        }

        return UserAchievement::query()
            ->where('user_id', $user->id)
            ->where('achievement_id', $achievementId)
            ->exists();
    }

    /**
     * @param  list<Achievement>  $achievements
     */
    private function sendNotification(User $user, array $achievements, ?string $dedupeKey = null): void
    {
        if ($achievements === []) {
            return;
        }

        $dedupeKey ??= count($achievements) === 1
            ? "achievement:{$user->id}:{$achievements[0]->slug}"
            : "achievement-batch:{$user->id}:{$this->batchToken}";

        $this->notifications->sendOnce(
            $user,
            'achievement_earned',
            $dedupeKey,
            new AchievementEarned($user, $achievements),
        );
    }
}
