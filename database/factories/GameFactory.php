<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $homeAbbr = fake()->randomElement(['MEX', 'BRA', 'ARG', 'USA', 'KOR']);
        $awayAbbr = fake()->randomElement(['RSA', 'CZE', 'FRA', 'GER', 'ESP']);
        $scheduledAt = fake()->dateTimeBetween('+1 day', '+60 days');

        return [
            'fifa_match_id' => (string) fake()->unique()->numerify('400######'),
            'match_number' => fake()->numberBetween(1, 104),
            'id_season' => '285023',
            'id_stage' => '289273',
            'id_group' => '289275',
            'stage_name' => 'First Stage',
            'group_name' => 'Group '.fake()->randomElement(['A', 'B', 'C', 'D']),
            'scheduled_at' => $scheduledAt,
            'local_scheduled_at' => $scheduledAt,
            'home_fifa_team_id' => (string) fake()->numerify('#####'),
            'home_name' => fake()->country(),
            'home_abbr' => $homeAbbr,
            'home_placeholder' => null,
            'away_fifa_team_id' => (string) fake()->numerify('#####'),
            'away_name' => fake()->country(),
            'away_abbr' => $awayAbbr,
            'away_placeholder' => null,
            'stadium_name' => fake()->city().' Stadium',
            'city_name' => fake()->city(),
            'match_status' => 1,
            'home_score' => null,
            'away_score' => null,
            'time_defined' => true,
            'is_final' => false,
            'payload' => [
                'IdMatch' => Str::uuid()->toString(),
                'Home' => [
                    'IdCountry' => $homeAbbr,
                    'Abbreviation' => $homeAbbr,
                ],
                'Away' => [
                    'IdCountry' => $awayAbbr,
                    'Abbreviation' => $awayAbbr,
                ],
            ],
            'synced_at' => now(),
        ];
    }

    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => now()->subDay(),
            'local_scheduled_at' => now()->subDay(),
        ]);
    }
}
