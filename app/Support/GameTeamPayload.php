<?php

namespace App\Support;

final class GameTeamPayload
{
    /**
     * @param  array<string, mixed>|null  $payloadSide
     * @return array{displayName: string, abbr: string|null, flagIconCode: string|null}
     */
    public static function forSide(
        ?string $name,
        ?string $abbr,
        ?string $placeholder,
        ?array $payloadSide,
    ): array {
        return [
            'displayName' => $name ?? $placeholder ?? '—',
            'abbr' => $abbr,
            'flagIconCode' => TeamFlagIcon::forTeam($abbr, $payloadSide),
        ];
    }
}
