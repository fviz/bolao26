<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\GameComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameComment>
 */
class GameCommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'game_id' => Game::factory(),
            'parent_id' => null,
            'body' => fake()->text(fake()->numberBetween(10, 120)),
        ];
    }

    public function replyTo(GameComment $parent): static
    {
        return $this->state(fn (): array => [
            'game_id' => $parent->game_id,
            'parent_id' => $parent->id,
        ]);
    }
}
