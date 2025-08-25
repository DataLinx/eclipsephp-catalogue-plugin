<?php

namespace Eclipse\Catalogue\Factories;

use Eclipse\Catalogue\Models\Property;
use Eclipse\Catalogue\Models\PropertyValue;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyValueFactory extends Factory
{
    protected $model = PropertyValue::class;

    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'value' => ['en' => $this->faker->word()],
            'sort' => $this->faker->numberBetween(0, 100),
            'info_url' => $this->faker->optional(0.3)->url(),
            'image' => $this->faker->optional(0.2)->imageUrl(200, 200),
        ];
    }

    public function forProperty(Property $property): static
    {
        return $this->state(fn (array $attributes) => [
            'property_id' => $property->id,
        ]);
    }
}
