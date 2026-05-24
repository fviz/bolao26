<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Services\Scoring\ScoreChampionPredictions;
use App\Services\Scoring\ScoreGamePredictions;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('bolao:score-games {--game= : Score a single game by ID}')]
#[Description('Score predictions for finished games')]
class ScoreBolaoGamesCommand extends Command
{
    public function handle(
        ScoreGamePredictions $scoreGamePredictions,
        ScoreChampionPredictions $scoreChampionPredictions,
    ): int {
        $gameId = $this->option('game');

        $query = Game::query()->where('is_final', true);

        if ($gameId !== null) {
            $query->whereKey($gameId);
        }

        $scored = 0;

        $query->each(function (Game $game) use ($scoreGamePredictions, $scoreChampionPredictions, &$scored): void {
            if ($scoreGamePredictions->score($game)) {
                $scored++;
                $this->line("Scored game #{$game->id}: {$game->matchTitle()}");

                if ($game->isTournamentFinal()) {
                    $scoreChampionPredictions->scoreForFinal($game->fresh());
                    $this->line('Scored champion predictions.');
                }
            }
        });

        $this->info("Scored {$scored} game(s).");

        return self::SUCCESS;
    }
}
