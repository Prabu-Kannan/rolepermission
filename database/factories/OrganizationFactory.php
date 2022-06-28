<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $types = ['recruiter', 'enterprise'];
        return [
            'uuid' => $this->faker->uuid,
            'name' => $this->faker->name,
            'type' => $this->faker->randomElement($types)
        ];
    }
}
