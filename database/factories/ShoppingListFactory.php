<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShoppingList>
 */
class ShoppingListFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'creation_date' => $this->faker->date(),
            'total_value' => $this->faker->randomFloat(2, 0, 1000),
            'market_name' => $this->faker->company,
            'is_completed' => false,
        ];
    }
}
