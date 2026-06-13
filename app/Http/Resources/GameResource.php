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
class GameResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $payload */
        $payload = $this->payload ?? [];

        return [
            'id' => $this->id,
            'matchTitle' => $this->matchTitle(),
            'matchNumber' => $this->match_number,
            'stageName' => $this->stage_name,
            'groupName' => $this->group_name,
            'scheduledAt' => $this->scheduled_at?->toIso8601String(),
            'localScheduledAt' => $this->local_scheduled_at?->toIso8601String(),
            'stadiumName' => $this->stadium_name,
            'cityName' => $this->city_name,
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
            'isKnockout' => $this->isKnockout(),
            'isFinal' => $this->is_final,
            'isBettingOpen' => $this->isBettingOpen(),
            'arePredictionsVisible' => $this->arePredictionsVisible(),
            'bettingClosesAt' => $this->bettingClosesAt()->toIso8601String(),
            'commentsCount' => $this->whenCounted('comments'),
            'result' => $this->when($this->is_final, fn (): array => [
                'homeScore' => $this->home_score,
                'awayScore' => $this->away_score,
                'penaltyWinner' => $this->penalty_winner,
            ]),
            'userPrediction' => $this->when(
                $this->relationLoaded('predictions'),
                fn () => $this->userPredictionFromRelation($request),
            ),
            'allPredictions' => $this->when(
                $this->relationLoaded('predictions'),
                fn () => $this->allPredictionsPayload($request),
            ),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function allPredictionsPayload(Request $request): array
    {
        $sorted = $this->predictions
            ->sortBy(fn (Prediction $prediction): string => $prediction->user->name)
            ->values();

        if ($this->arePredictionsVisible()) {
            return PredictionResource::collection($sorted)->resolve($request);
        }

        return $sorted
            ->map(function (Prediction $prediction) use ($request): array {
                $isCurrentUser = $request->user()?->id === $prediction->user_id;

                $payload = [
                    'userId' => $prediction->user_id,
                    'userName' => $prediction->user->name,
                    'isCurrentUser' => $isCurrentUser,
                ];

                if ($isCurrentUser) {
                    $payload['homeScore'] = $prediction->home_score;
                    $payload['awayScore'] = $prediction->away_score;
                    $payload['penaltyWinner'] = $prediction->penalty_winner;
                }

                return $payload;
            })
            ->all();
    }

    /**
     * @return array{homeScore: int, awayScore: int, penaltyWinner: string|null, points: int|null}|null
     */
    private function userPredictionFromRelation(Request $request): ?array
    {
        /** @var Prediction|null $prediction */
        $prediction = $this->predictions->firstWhere('user_id', $request->user()?->id);

        if ($prediction === null) {
            return null;
        }

        return [
            'homeScore' => $prediction->home_score,
            'awayScore' => $prediction->away_score,
            'penaltyWinner' => $prediction->penalty_winner,
            'points' => $prediction->points,
        ];
    }
}
