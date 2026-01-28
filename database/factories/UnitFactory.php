<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
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
            'code' => $this->faker->unique()->bothify('UNIT-###'),
            'name' => $this->faker->jobTitle() . ' Unit',
            // 'head_employee_id' => Employee::factory(), // Recursive dependency risk. Leave null or handle?
        ];
    }
}
