<?php

namespace Eclipse\Catalogue\Factories;

use Eclipse\Catalogue\Models\MeasureUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Eclipse\Catalogue\Models\MeasureUnit>
 */
class MeasureUnitFactory extends Factory
{
    protected $model = MeasureUnit::class;

    public function definition(): array
    {
        $units = ['kg', 'g', 'lbs', 'oz', 'liter', 'ml', 'pieces', 'pcs', 'm', 'cm', 'mm', 'ft', 'in'];

        return [
            'name' => $this->faker->randomElement($units),
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
