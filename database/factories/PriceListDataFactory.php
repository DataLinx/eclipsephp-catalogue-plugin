<?php

namespace Eclipse\Catalogue\Factories;

use Eclipse\Catalogue\Models\PriceList;
use Eclipse\Catalogue\Models\PriceListData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Eclipse\Catalogue\Models\PriceListData>
 */
class PriceListDataFactory extends Factory
{
    protected $model = PriceListData::class;

    public function definition(): array
    {
        $definition = [
            'price_list_id' => PriceList::factory(),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'is_default' => false,
            'is_default_purchase' => false,
        ];

        // Add tenant foreign key if configured
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

    public function defaultSelling(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
            'is_default_purchase' => false,
        ]);
    }

    public function defaultPurchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => false,
            'is_default_purchase' => true,
        ]);
    }
}
