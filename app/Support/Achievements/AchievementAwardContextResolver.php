<?php

namespace App\Support\Achievements;

use App\Models\Achievement;
use App\Models\Game;
use App\Models\UserAchievement;
use App\Support\GameTeamPayload;
use App\Support\TeamFlagIcon;

final class AchievementAwardContextResolver
{
    /**
     * @var list<string>
     */
    private const array ON_PREDICTION_SLUGS = [
        'primeiro-chute',
        'atrasado-do-enem',
        'gabaritando-a-agenda',
    ];

    /**
     * @return array<string, mixed>|null
     */
    public static function resolve(UserAchievement $userAchievement, Achievement $achievement): ?array
    {
        $context = $userAchievement->context;

        if (! is_array($context) || $context === []) {
            return null;
        }

        if (isset($context['match_day'])) {
            return self::resolveMatchDay($context);
        }

        if (isset($context['fifa_team_id'], $context['player_id'])) {
            return self::resolveChampionAndTopScorer($context);
        }

        if (isset($context['fifa_team_id'])) {
            return self::resolveChampion($context);
        }

        if (isset($context['player_id'])) {
            return self::resolveTopScorer($context);
        }

        if (isset($context['game_id'])) {
            return self::resolveGame((int) $context['game_id'], $achievement->slug);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private static function resolveMatchDay(array $context): array
    {
        $matchDay = (string) $context['match_day'];

        return [
            'type' => 'match_day',
            'matchDay' => $matchDay,
            'games' => MatchDayGames::forDate($matchDay)
                ->map(fn (Game $game): array => self::compactGame($game))
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private static function resolveChampion(array $context): array
    {
        return [
            'type' => 'champion',
            'team' => self::teamPayload(
                (string) $context['fifa_team_id'],
                (string) ($context['team_name'] ?? $context['fifa_team_id']),
                isset($context['team_abbr']) ? (string) $context['team_abbr'] : null,
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private static function resolveTopScorer(array $context): array
    {
        return [
            'type' => 'top_scorer',
            'player' => [
                'id' => (string) $context['player_id'],
                'name' => (string) ($context['player_name'] ?? $context['player_id']),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private static function resolveChampionAndTopScorer(array $context): array
    {
        return [
            'type' => 'champion_and_top_scorer',
            'team' => self::teamPayload(
                (string) $context['fifa_team_id'],
                (string) ($context['team_name'] ?? $context['fifa_team_id']),
                isset($context['team_abbr']) ? (string) $context['team_abbr'] : null,
            ),
            'player' => [
                'id' => (string) $context['player_id'],
                'name' => (string) ($context['player_name'] ?? $context['player_id']),
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function resolveGame(int $gameId, string $slug): ?array
    {
        $game = Game::query()->find($gameId);

        if ($game === null) {
            return null;
        }

        return [
            'type' => 'game',
            'trigger' => in_array($slug, self::ON_PREDICTION_SLUGS, true)
                ? 'on_prediction'
                : 'after_match',
            'game' => self::compactGame($game),
        ];
    }

    /**
     * @return array{id: int, matchTitle: string, stageName: string|null, home: array{displayName: string, abbr: string|null, flagIconCode: string|null}, away: array{displayName: string, abbr: string|null, flagIconCode: string|null}}
     */
    private static function compactGame(Game $game): array
    {
        /** @var array<string, mixed> $payload */
        $payload = $game->payload ?? [];

        return [
            'id' => $game->id,
            'matchTitle' => $game->matchTitle(),
            'stageName' => $game->stage_name,
            'home' => GameTeamPayload::forSide(
                $game->home_name,
                $game->home_abbr,
                $game->home_placeholder,
                is_array($payload['Home'] ?? null) ? $payload['Home'] : null,
            ),
            'away' => GameTeamPayload::forSide(
                $game->away_name,
                $game->away_abbr,
                $game->away_placeholder,
                is_array($payload['Away'] ?? null) ? $payload['Away'] : null,
            ),
        ];
    }

    /**
     * @return array{name: string, abbr: string|null, flagIconCode: string|null}
     */
    private static function teamPayload(string $fifaTeamId, string $name, ?string $abbr): array
    {
        return [
            'name' => $name,
            'abbr' => $abbr,
            'flagIconCode' => TeamFlagIcon::forTeam($abbr, ['IdTeam' => $fifaTeamId]),
        ];
    }
}
