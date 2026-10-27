<?php

namespace Database\Factories;

use App\Models\GameMatch;
use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GameMatch> */
class GameMatchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'fecha' => now(),
            'ganador_id' => Player::factory(),
            'perdedor_id' => Player::factory(),
            'victorias_ganador' => 3,
            'victorias_perdedor' => fake()->numberBetween(0, 2),
        ];
    }
}
