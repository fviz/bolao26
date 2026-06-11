<?php

namespace App\Console\Commands;

use App\Services\Fifa\Exceptions\FifaApiException;
use App\Services\Fifa\SyncFifaGames;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
        Log::info('Starting FIFA game results sync.');

        try {
            $updated = $syncFifaGames->syncResults();
        } catch (FifaApiException $exception) {
            Log::error('FIFA game results sync failed.', [
                'message' => $exception->getMessage(),
            ]);

            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        Log::info("Updated {$updated} game results from FIFA.");

        $this->info("Updated {$updated} game results from FIFA.");

        return self::SUCCESS;
    }
}
