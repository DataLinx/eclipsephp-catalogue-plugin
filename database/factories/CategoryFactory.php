<?php

namespace Eclipse\Catalogue\Factories;

use Eclipse\Catalogue\Models\Category;
use Eclipse\Core\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $englishName = mb_ucfirst(fake()->words(2, true));
        $slovenianName = 'SI: '.$englishName;

        $englishShortDesc = fake()->sentence();
        $slovenianShortDesc = 'SI: '.$englishShortDesc;

        $englishDesc = fake()->paragraphs(3, true);
        $slovenianDesc = 'SI: '.$englishDesc;

        return [
            'name' => [
                'en' => $englishName,
                'sl' => $slovenianName,
            ],
            'parent_id' => null,
            'image' => fake()->optional(0.3)->imageUrl(400, 300, 'business', true, 'Category'),
            'sort' => fake()->randomNumber(),
            'is_active' => fake()->boolean(),
            'code' => fake()->optional()->bothify('CAT-####'),
            'recursive_browsing' => fake()->boolean(),
            'sef_key' => [
                'en' => Str::slug($englishName),
                'sl' => Str::slug($slovenianName),
            ],
            'short_desc' => [
                'en' => $englishShortDesc,
                'sl' => $slovenianShortDesc,
            ],
            'description' => [
                'en' => $englishDesc,
                'sl' => $slovenianDesc,
            ],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'site_id' => Site::inRandomOrder()->first()?->id ?? Site::factory()->create()->id,
        ];
    }

    public function parent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'parent_id' => null,
        ]);
    }

    public function child(?Category $parent = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'parent_id' => $parent?->id ?? Category::factory()->parent()->create()->id,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
