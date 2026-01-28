<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
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
            'unit_id' => Unit::factory(),
            'user_id' => User::factory(),
            'nip' => $this->faker->unique()->numerify('##########'),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'position' => 'Staff',
            'rank' => 'IV/a',
            'grade' => 'Pembina',
            'employment_status' => 'PNS',
            'is_active' => true,
        ];
    }
}
