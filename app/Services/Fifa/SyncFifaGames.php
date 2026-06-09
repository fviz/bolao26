<?php

namespace App\Services\Fifa;

use App\Models\Game;
use App\Services\Fifa\Exceptions\FifaApiException;
use App\Services\Scoring\ScoreChampionPredictions;
use App\Services\Scoring\ScoreGamePredictions;
use Illuminate\Support\Collection;

class SyncFifaGames
{
    public function __construct(
        private readonly FifaCalendarClient $client,
        private readonly FifaMatchMapper $mapper,
        private readonly ScoreGamePredictions $scoreGamePredictions,
        private readonly ScoreChampionPredictions $scoreChampionPredictions,
    ) {}

    /**
     * @throws FifaApiException
     */
    public function syncAll(): int
    {
        $matches = $this->client->matches();
        $synced = 0;

        foreach ($matches as $match) {
            $attributes = $this->mapper->toAttributes($match);

            Game::query()->updateOrCreate(
                ['fifa_match_id' => $attributes['fifa_match_id']],
                $attributes,
            );

            $synced++;
        }

        return $synced;
    }

    /**
     * @throws FifaApiException
     */
    public function syncResults(): int
    {
        $candidates = Game::query()
            ->resultsSyncCandidates()
            ->get();

        if ($candidates->isEmpty()) {
            return 0;
        }

        $matchesById = $this->matchesIndexedById($this->client->matches());
        $updated = 0;

        foreach ($candidates as $game) {
            $match = $matchesById->get($game->fifa_match_id);

            if ($match === null) {
                continue;
            }

            $attributes = $this->mapper->toAttributes($match);
            $game->fill($attributes);
            $game->save();

            if ($this->shouldScoreGame($game)) {
                $this->scoreGamePredictions->score($game->fresh());

                if ($game->fresh()->isTournamentFinal()) {
                    $this->scoreChampionPredictions->scoreForFinal($game->fresh());
                }
            }

            $updated++;
        }

        return $updated;
    }

    /**
     * @param  list<array<string, mixed>>  $matches
     * @return Collection<string, array<string, mixed>>
     */
    private function matchesIndexedById(array $matches): Collection
    {
        return collect($matches)->keyBy(fn (array $match): string => (string) $match['IdMatch']);
    }

    private function shouldScoreGame(Game $game): bool
    {
        if (! $game->is_final) {
            return false;
        }

        return $game->wasChanged([
            'is_final',
            'home_score',
            'away_score',
            'home_penalty_score',
            'away_penalty_score',
            'penalty_winner',
        ]);
    }
}
