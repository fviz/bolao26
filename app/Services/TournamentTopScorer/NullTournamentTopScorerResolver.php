<?php

namespace App\Services\TournamentTopScorer;

use App\Contracts\TournamentTopScorerResolver;

/**
 * Stub resolver — returns null until top scorer source is implemented.
 *
 * TODO: Implement via FIFA API sync or config('bolao.tournament_top_scorer_player_id').
 */
final class NullTournamentTopScorerResolver implements TournamentTopScorerResolver
{
    public function resolve(): ?string
    {
        return null;
    }
}
