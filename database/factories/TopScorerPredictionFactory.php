<?php

namespace Database\Factories;

use App\Models\TopScorerPrediction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TopScorerPrediction>
 */
class TopScorerPredictionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'player_id' => 'brazil-neymar',
            'points' => null,
            'scored_at' => null,
        ];
    }
}
