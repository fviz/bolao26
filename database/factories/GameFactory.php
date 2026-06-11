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

    public function live(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => now()->subMinutes(30),
            'local_scheduled_at' => now()->subMinutes(30),
            'is_final' => false,
            'home_score' => null,
            'away_score' => null,
        ]);
    }

    public function knockout(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_group' => null,
            'group_name' => null,
            'stage_name' => 'Round of 16',
        ]);
    }

    public function finalMatch(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_group' => null,
            'group_name' => null,
            'stage_name' => 'Final',
        ]);
    }

    /**
     * @param  array{home_score?: int, away_score?: int, home_penalty_score?: int|null, away_penalty_score?: int|null, penalty_winner?: string|null}  $result
     */
    public function finished(array $result = []): static
    {
        return $this->past()->state(fn (array $attributes) => array_merge([
            'home_score' => 2,
            'away_score' => 1,
            'home_penalty_score' => null,
            'away_penalty_score' => null,
            'penalty_winner' => null,
            'match_status' => 4,
            'is_final' => true,
        ], $result));
    }
}
