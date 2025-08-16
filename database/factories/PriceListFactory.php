<?php

namespace Eclipse\Catalogue\Factories;

use Eclipse\Catalogue\Models\PriceList;
use Eclipse\World\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Eclipse\Catalogue\Models\PriceList>
 */
class PriceListFactory extends Factory
{
    protected $model = PriceList::class;

    public function definition(): array
    {
        // Get existing currencies or create a default one
        $currencies = Currency::all();
        if ($currencies->isEmpty()) {
            Currency::create(['id' => 'EUR', 'name' => 'Euro', 'is_active' => true]);
            $currencies = Currency::all();
        }

        return [
            'currency_id' => $currencies->random()->id,
            'name' => $this->faker->words(2, true).' Price List',
            'code' => strtoupper($this->faker->unique()->lexify('PL???')),
            'tax_included' => $this->faker->boolean(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function withTaxIncluded(): static
    {
        return $this->state(fn (array $attributes) => [
            'tax_included' => true,
        ]);
    }

    public function withoutTax(): static
    {
        return $this->state(fn (array $attributes) => [
            'tax_included' => false,
        ]);
    }
}
