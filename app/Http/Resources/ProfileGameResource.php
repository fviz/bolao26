<?php

namespace App\Http\Resources;

use App\Models\Game;
use App\Models\Prediction;
use App\Support\GameTeamPayload;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Game
 */
class ProfileGameResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $payload */
        $payload = $this->payload ?? [];

        /** @var Prediction|null $prediction */
        $prediction = $this->predictions->first();

        return [
            'id' => $this->id,
            'matchTitle' => $this->matchTitle(),
            'stageName' => $this->stage_name,
            'scheduledAt' => $this->scheduled_at?->toIso8601String(),
            'home' => GameTeamPayload::forSide(
                $this->home_name,
                $this->home_abbr,
                $this->home_placeholder,
                is_array($payload['Home'] ?? null) ? $payload['Home'] : null,
            ),
            'away' => GameTeamPayload::forSide(
                $this->away_name,
                $this->away_abbr,
                $this->away_placeholder,
                is_array($payload['Away'] ?? null) ? $payload['Away'] : null,
            ),
            'result' => [
                'homeScore' => $this->home_score,
                'awayScore' => $this->away_score,
                'penaltyWinner' => $this->penalty_winner,
            ],
            'prediction' => $prediction !== null ? [
                'homeScore' => $prediction->home_score,
                'awayScore' => $prediction->away_score,
                'penaltyWinner' => $prediction->penalty_winner,
                'points' => $prediction->points,
            ] : null,
        ];
    }
}
