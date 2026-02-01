<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'is_password_reset' => true,
        ];
    }

    public function withEmployee(): static
    {
        return $this->afterCreating(function ($user) {
            $organization = \App\Models\Organization::factory()->create();
            $unit = \App\Models\Unit::factory()->create([
                'organization_id' => $organization->id,
            ]);
            $user->employee()->create([
                'organization_id' => $organization->id,
                'unit_id' => $unit->id,
                'nip' => sprintf('%018d', random_int(100000000000000000, 999999999999999999)),
                'name' => $user->name,
                'email' => $user->email,
                'user_id' => $user->id,
                'position' => 'Staff',
                'rank' => 'IV/a',
                'grade' => 'Pembina',
                'employment_status' => 'PNS',
                'approval_level' => 1,
                'is_active' => true,
            ]);
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
