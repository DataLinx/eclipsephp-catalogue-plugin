<?php

namespace Eclipse\Catalogue\Factories;

use Eclipse\Catalogue\Models\ProductTypeData;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductTypeDataFactory extends Factory
{
    protected $model = ProductTypeData::class;

    public function definition(): array
    {
        return [
            'is_active' => true,
            'is_default' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function notDefault(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => false,
        ]);
    }
}
