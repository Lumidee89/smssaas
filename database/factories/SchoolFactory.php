<?php

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<School>
 */
class SchoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Academy',
            'subdomain' => fake()->unique()->slug(2),
            'institution_type' => 'secondary',
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'theme_color' => '#06322C',
            'currency' => 'NGN',
            'timezone' => 'Africa/Lagos',
            'is_active' => true,
            'subscription_end_date' => now()->addYear(),
        ];
    }
}
