<?php

namespace Eclipse\Catalogue\Factories;

use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Models\ProductData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Eclipse\Catalogue\Models\ProductData>
 */
class ProductDataFactory extends Factory
{
    protected $model = ProductData::class;

    public function definition(): array
    {
        $definition = [
            'product_id' => Product::factory(),
            'sorting_label' => $this->faker->optional()->word(),
            'is_active' => $this->faker->boolean(90),
            'available_from_date' => $this->faker->optional()->dateTimeBetween('now', '+3 months'),
            'has_free_delivery' => $this->faker->boolean(20),
            'stock' => $this->faker->optional(0.7)->randomFloat(5, 0, 10000),
            'min_stock' => $this->faker->optional(0.5)->randomFloat(5, 0, 100),
            'date_stocked' => $this->faker->optional(0.6)->date('Y-m-d', '-1 year'),
        ];

        if (config('eclipse-catalogue.tenancy.foreign_key')) {
            $tenantModel = config('eclipse-catalogue.tenancy.model');
            if (class_exists($tenantModel)) {
                $definition[config('eclipse-catalogue.tenancy.foreign_key')] = $tenantModel::factory();
            }
        }

        return $definition;
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

    public function withStock(?float $stock = null, ?float $minStock = null): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $stock ?? $this->faker->randomFloat(5, 1, 1000),
            'min_stock' => $minStock ?? $this->faker->randomFloat(5, 0, 50),
            'date_stocked' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }
}
