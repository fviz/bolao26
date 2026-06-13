<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserAchievement;
use App\Services\Achievements\AchievementBackfiller;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('achievements:backfill {--user= : Backfill a single user by ID} {--notify : Send notifications for newly awarded medals}')]
#[Description('Award achievements retroactively for past predictions and scored games')]
class BackfillAchievementsCommand extends Command
{
    public function handle(AchievementBackfiller $backfiller): int
    {
        $userId = $this->option('user');
        $user = $userId !== null ? User::query()->find($userId) : null;

        if ($userId !== null && $user === null) {
            $this->error("User #{$userId} not found.");

            return self::FAILURE;
        }

        $before = UserAchievement::query()
            ->when($user !== null, fn ($query) => $query->where('user_id', $user->id))
            ->count();

        $this->info('Backfilling achievements...');

        $backfiller->backfill($user, notify: (bool) $this->option('notify'));

        $after = UserAchievement::query()
            ->when($user !== null, fn ($query) => $query->where('user_id', $user->id))
            ->count();

        $newAwards = $after - $before;

        $this->info("Backfill complete. {$newAwards} new medal(s) awarded ({$after} total).");

        return self::SUCCESS;
    }
}
