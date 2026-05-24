<?php

namespace App\Services\Fifa;

use App\Models\Game;
use App\Services\Fifa\Exceptions\FifaApiException;
use Illuminate\Support\Collection;

class SyncFifaGames
{
    public function __construct(
        private readonly FifaCalendarClient $client,
        private readonly FifaMatchMapper $mapper,
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
            ->notFinal()
            ->kickoffPassed()
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
}
