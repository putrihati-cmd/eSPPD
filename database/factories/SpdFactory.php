<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Spd;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpdFactory extends Factory
{
    protected $model = Spd::class;

    public function definition(): array
    {
        $departureDate = $this->faker->dateTimeBetween('+1 week', '+2 months');
        $returnDate = (clone $departureDate)->modify('+' . $this->faker->numberBetween(1, 7) . ' days');

        return [
            'organization_id' => Organization::factory(),
            'unit_id' => Unit::factory(),
            'employee_id' => Employee::factory(),
            'spt_number' => 'SPT/' . date('Y/m') . '/' . str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'spd_number' => 'SPD/' . date('Y/m') . '/' . str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'destination' => $this->faker->city(),
            'purpose' => $this->faker->sentence(10),
            'invitation_number' => $this->faker->optional()->numerify('UND-####'),
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'duration' => $departureDate->diff($returnDate)->days + 1,
            'budget_id' => Budget::factory(),
            'estimated_cost' => $this->faker->numberBetween(1000000, 10000000),
            'transport_type' => $this->faker->randomElement(['pesawat', 'kereta', 'bus', 'mobil_dinas']),
            'needs_accommodation' => $this->faker->boolean(),
            'status' => 'draft',
            'created_by' => \App\Models\User::factory(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'draft']);
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'submitted_at' => now()->subDays(2),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'submitted_at' => now()->subDays(10),
            'completed_at' => now(),
        ]);
    }
}
