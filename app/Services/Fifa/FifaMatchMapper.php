<?php

namespace App\Services\Fifa;

use Illuminate\Support\Carbon;

class FifaMatchMapper
{
    private const string DEFAULT_LOCALE = 'en-GB';

    /**
     * @param  array<string, mixed>  $match
     * @return array<string, mixed>
     */
    public function toAttributes(array $match): array
    {
        $homeScore = $this->normalizeScore($match['HomeTeamScore'] ?? null);
        $awayScore = $this->normalizeScore($match['AwayTeamScore'] ?? null);
        $matchStatus = (int) ($match['MatchStatus'] ?? 0);

        return [
            'fifa_match_id' => (string) $match['IdMatch'],
            'match_number' => isset($match['MatchNumber']) ? (int) $match['MatchNumber'] : null,
            'id_season' => isset($match['IdSeason']) ? (string) $match['IdSeason'] : null,
            'id_stage' => isset($match['IdStage']) ? (string) $match['IdStage'] : null,
            'id_group' => isset($match['IdGroup']) ? (string) $match['IdGroup'] : null,
            'stage_name' => $this->localizedDescription($match['StageName'] ?? []),
            'group_name' => $this->localizedDescription($match['GroupName'] ?? []),
            'scheduled_at' => Carbon::parse($match['Date']),
            'local_scheduled_at' => isset($match['LocalDate'])
                ? Carbon::parse($match['LocalDate'])
                : null,
            'home_fifa_team_id' => $this->teamId($match['Home'] ?? []),
            'home_name' => $this->teamName($match['Home'] ?? []),
            'home_abbr' => $this->teamAbbreviation($match['Home'] ?? []),
            'home_placeholder' => isset($match['PlaceHolderA']) ? (string) $match['PlaceHolderA'] : null,
            'away_fifa_team_id' => $this->teamId($match['Away'] ?? []),
            'away_name' => $this->teamName($match['Away'] ?? []),
            'away_abbr' => $this->teamAbbreviation($match['Away'] ?? []),
            'away_placeholder' => isset($match['PlaceHolderB']) ? (string) $match['PlaceHolderB'] : null,
            'stadium_name' => $this->localizedDescription($match['Stadium']['Name'] ?? []),
            'city_name' => $this->localizedDescription($match['Stadium']['CityName'] ?? []),
            'match_status' => $matchStatus,
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'time_defined' => (bool) ($match['TimeDefined'] ?? true),
            'is_final' => $this->determineIsFinal($matchStatus, $homeScore, $awayScore),
            'payload' => $match,
            'synced_at' => now(),
        ];
    }

    /**
     * @param  list<array{Locale?: string, Description?: string}>|array<int, array<string, mixed>>  $items
     */
    private function localizedDescription(array $items): ?string
    {
        foreach ($items as $item) {
            if (($item['Locale'] ?? null) === self::DEFAULT_LOCALE) {
                return isset($item['Description']) ? (string) $item['Description'] : null;
            }
        }

        $first = $items[0] ?? null;

        return isset($first['Description']) ? (string) $first['Description'] : null;
    }

    /**
     * @param  array<string, mixed>  $team
     */
    private function teamId(array $team): ?string
    {
        return isset($team['IdTeam']) ? (string) $team['IdTeam'] : null;
    }

    /**
     * @param  array<string, mixed>  $team
     */
    private function teamName(array $team): ?string
    {
        return $this->localizedDescription($team['TeamName'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $team
     */
    private function teamAbbreviation(array $team): ?string
    {
        return isset($team['Abbreviation']) ? (string) $team['Abbreviation'] : null;
    }

    private function normalizeScore(mixed $score): ?int
    {
        if ($score === null) {
            return null;
        }

        return (int) $score;
    }

    private function determineIsFinal(int $matchStatus, ?int $homeScore, ?int $awayScore): bool
    {
        if ($homeScore !== null && $awayScore !== null && $matchStatus !== 1) {
            return true;
        }

        return in_array($matchStatus, [3, 4, 7], true);
    }
}
