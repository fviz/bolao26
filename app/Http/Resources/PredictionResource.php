<?php

namespace App\Http\Resources;

use App\Models\Prediction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Prediction
 */
class PredictionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'userId' => $this->user_id,
            'userName' => $this->user->name,
            'homeScore' => $this->home_score,
            'awayScore' => $this->away_score,
            'penaltyWinner' => $this->penalty_winner,
            'points' => $this->points,
            'isCurrentUser' => $request->user()?->id === $this->user_id,
        ];
    }
}
