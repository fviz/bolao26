<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Achievements\AchievementContextEnricher;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('achievements:enrich-context {--user= : Enrich achievements for a single user by ID}')]
#[Description('Backfill award context on existing user achievements')]
class EnrichAchievementContextCommand extends Command
{
    public function handle(AchievementContextEnricher $enricher): int
    {
        $userId = $this->option('user');
        $user = $userId !== null ? User::query()->find($userId) : null;

        if ($userId !== null && $user === null) {
            $this->error("User #{$userId} not found.");

            return self::FAILURE;
        }

        $this->info('Enriching achievement context...');

        $updated = $enricher->enrich($user);

        $this->info("Enrichment complete. {$updated} user achievement(s) updated.");

        return self::SUCCESS;
    }
}
