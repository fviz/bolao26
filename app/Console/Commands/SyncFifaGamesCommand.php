<?php

namespace App\Console\Commands;

use App\Services\Fifa\Exceptions\FifaApiException;
use App\Services\Fifa\SyncFifaGames;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncFifaGamesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'games:sync-fifa';

    /**
     * @var string
     */
    protected $description = 'Sync all World Cup matches from the FIFA calendar API';

    public function handle(SyncFifaGames $syncFifaGames): int
    {
        Log::info('Starting FIFA games sync.');

        try {
            $synced = $syncFifaGames->syncAll();
        } catch (FifaApiException $exception) {
            Log::error('FIFA games sync failed.', [
                'message' => $exception->getMessage(),
            ]);

            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        Log::info("Synced {$synced} games from FIFA.");

        $this->info("Synced {$synced} games from FIFA.");

        return self::SUCCESS;
    }
}
