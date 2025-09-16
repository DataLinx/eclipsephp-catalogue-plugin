<?php

namespace Eclipse\Catalogue\Factories;

use Eclipse\Catalogue\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->slug(2),
            'name' => ['en' => $this->faker->words(2, true)],
            'description' => ['en' => $this->faker->sentence()],
            'internal_name' => $this->faker->words(3, true),
            'is_active' => true,
            'is_global' => $this->faker->boolean(20), // 20% chance of being global
            'max_values' => $this->faker->randomElement([1, 2, 5]),
            'enable_sorting' => $this->faker->boolean(30),
            'is_filter' => $this->faker->boolean(40),
        ];
    }

    public function global(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_global' => true,
        ]);
    }

    public function singleValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_values' => 1,
        ]);
    }

    public function multipleValues(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_values' => $this->faker->numberBetween(2, 10),
        ]);
    }

    public function filter(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_filter' => true,
        ]);
    }
}
