<?php

namespace App\Services\Fifa;

use App\Services\Fifa\Exceptions\FifaApiException;
use Illuminate\Support\Facades\Http;

class FifaCalendarClient
{
    /**
     * @return list<array<string, mixed>>
     *
     * @throws FifaApiException
     */
    public function matches(): array
    {
        $response = Http::fifa()->get('calendar/matches', [
            'language' => config('fifa.language'),
            'count' => config('fifa.match_count'),
            'idSeason' => config('fifa.season_id'),
        ]);

        if ($response->failed()) {
            throw new FifaApiException(
                'FIFA API request failed with status '.$response->status(),
            );
        }

        $results = $response->json('Results');

        if (! is_array($results)) {
            throw new FifaApiException('FIFA API response is missing Results array.');
        }

        return $results;
    }
}
