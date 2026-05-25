<?php

namespace App\Console\Commands;

use App\Services\Scoring\ScoreTopScorerPredictions;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('bolao:score-top-scorer {--player= : WorldCupPlayers id of the tournament top scorer}')]
#[Description('Score top scorer predictions when the tournament top scorer is known')]
class ScoreTopScorerPredictionsCommand extends Command
{
    public function handle(ScoreTopScorerPredictions $scoreTopScorerPredictions): int
    {
        $playerId = $this->option('player');

        if ($playerId === null || $playerId === '') {
            if (! $scoreTopScorerPredictions->score()) {
                $this->error('No top scorer available. Pass --player={id} or implement TournamentTopScorerResolver.');

                return self::FAILURE;
            }

            $this->info('Scored top scorer predictions.');

            return self::SUCCESS;
        }

        if (! $scoreTopScorerPredictions->score($playerId)) {
            $this->error("Could not score top scorer predictions for player id: {$playerId}");

            return self::FAILURE;
        }

        $this->info("Scored top scorer predictions for {$playerId}.");

        return self::SUCCESS;
    }
}
