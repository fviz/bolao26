<?php

namespace App\Contracts;

interface TournamentTopScorerResolver
{
    /**
     * Resolve the tournament top scorer as a WorldCupPlayers id, or null if unknown.
     */
    public function resolve(): ?string;
}
