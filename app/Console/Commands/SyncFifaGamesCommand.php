<?php

namespace App\Console\Commands;

use App\Services\Fifa\Exceptions\FifaApiException;
use App\Services\Fifa\SyncFifaGames;
use Illuminate\Console\Command;

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
        try {
            $synced = $syncFifaGames->syncAll();
        } catch (FifaApiException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Synced {$synced} games from FIFA.");

        return self::SUCCESS;
    }
}
