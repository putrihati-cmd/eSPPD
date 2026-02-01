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
            'nip' => sprintf('%018d', random_int(100000000000000000, 999999999999999999)),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'position' => 'Staff',
            'rank' => 'IV/a',
            'grade' => 'Pembina',
            'employment_status' => 'PNS',
            'approval_level' => 1,
            'is_active' => true,
        ];
    }
}
