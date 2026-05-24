<?php

namespace Database\Factories;

use App\Models\ChampionPrediction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChampionPrediction>
 */
class ChampionPredictionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'fifa_team_id' => (string) fake()->numerify('#####'),
            'points' => null,
            'scored_at' => null,
        ];
    }
}
