<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Organization;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'year' => date('Y'),
            'code' => 'MAK-' . $this->faker->unique()->numberBetween(1000, 9999),
            'name' => 'Anggaran ' . $this->faker->words(3, true),
            'total_budget' => $this->faker->numberBetween(50000000, 500000000),
            'used_budget' => 0,
            'is_active' => true,
        ];
    }
}
