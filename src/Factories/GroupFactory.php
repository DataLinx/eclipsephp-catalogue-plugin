<?php

namespace Eclipse\Catalogue\Factories;

use Eclipse\Catalogue\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key', 'site_id');

        return [
            $tenantFK => null, // Will be set when creating
            'code' => $this->faker->unique()->slug(2),
            'name' => $this->faker->words(2, true),
            'is_active' => true,
            'is_browsable' => false,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function browsable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_browsable' => true,
        ]);
    }

    public function notBrowsable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_browsable' => false,
        ]);
    }
}
