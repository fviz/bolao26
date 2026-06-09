<?php

namespace App\Console\Commands;

use App\Services\Fifa\Exceptions\FifaApiException;
use App\Services\Fifa\SyncFifaGames;
use Illuminate\Console\Command;

class SyncFifaGameResultsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'games:sync-fifa-results';

    /**
     * @var string
     */
    protected $description = 'Update scores and status for kicked-off matches, including reevaluated finals';

    public function handle(SyncFifaGames $syncFifaGames): int
    {
        try {
            $updated = $syncFifaGames->syncResults();
        } catch (FifaApiException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Updated {$updated} game results from FIFA.");

        return self::SUCCESS;
    }
}
