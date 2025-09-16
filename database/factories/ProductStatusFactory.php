<?php

namespace Eclipse\Catalogue\Factories;

use Eclipse\Catalogue\Enums\StructuredData\ItemAvailability;
use Eclipse\Catalogue\Models\ProductStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductStatusFactory extends Factory
{
    protected $model = ProductStatus::class;

    public function definition(): array
    {
        return [
            'site_id' => 1, // Default site ID, can be overridden
            'code' => $this->faker->unique()->slug(2),
            'title' => ['en' => $this->faker->words(2, true)],
            'description' => ['en' => $this->faker->sentence()],
            'label_type' => $this->faker->randomElement(['gray', 'danger', 'success', 'warning', 'info', 'primary']),
            'shown_in_browse' => $this->faker->boolean(80), // 80% chance of being shown
            'allow_price_display' => $this->faker->boolean(90), // 90% chance of allowing price display
            'allow_sale' => $this->faker->boolean(85), // 85% chance of allowing sale
            'is_default' => false, // Default to false, can be overridden
            'priority' => $this->faker->numberBetween(1, 100),
            'sd_item_availability' => $this->faker->randomElement([
                ItemAvailability::DISCONTINUED->value,
                ItemAvailability::IN_STOCK->value,
                ItemAvailability::IN_STORE_ONLY->value,
                ItemAvailability::LIMITED_AVAILABILITY->value,
                ItemAvailability::ONLINE_ONLY->value,
                ItemAvailability::OUT_OF_STOCK->value,
                ItemAvailability::PREORDER->value,
                ItemAvailability::PRESALE->value,
                ItemAvailability::SOLD_OUT->value,
            ]),
            'skip_stock_qty_check' => $this->faker->boolean(20), // 20% chance of skipping stock check
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'active',
            'title' => ['en' => 'Active'],
            'label_type' => 'success',
            'shown_in_browse' => true,
            'allow_price_display' => true,
            'allow_sale' => true,
            'is_default' => false,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'inactive',
            'title' => ['en' => 'Inactive'],
            'label_type' => 'danger',
            'shown_in_browse' => false,
            'allow_price_display' => false,
            'allow_sale' => false,
            'is_default' => false,
        ]);
    }

    public function noPrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'no_price',
            'title' => ['en' => 'No Price'],
            'label_type' => 'warning',
            'allow_price_display' => false,
            'allow_sale' => false, // Must be false when allow_price_display is false
        ]);
    }

    public function forSite(int $siteId): static
    {
        return $this->state(fn (array $attributes) => [
            'site_id' => $siteId,
        ]);
    }
}
