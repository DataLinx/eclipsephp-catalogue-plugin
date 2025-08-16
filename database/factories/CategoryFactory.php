<?php

namespace Eclipse\Catalogue\Factories;

use Eclipse\Catalogue\Models\Category;
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
            'image' => self::generateCategoryImage($englishName),
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
            'site_id' => null,
        ];
    }

    private static function generateCategoryImage(string $name): ?string
    {
        $colors = ['3B82F6', '10B981', 'F59E0B', 'EF4444', '8B5CF6', '06B6D4', 'F97316', 'EC4899'];
        $backgrounds = ['E0F2FE', 'ECFDF5', 'FFFBEB', 'FEF2F2', 'F3E8FF', 'ECFEFF', 'FFF7ED', 'FDF2F8'];

        $color = fake()->randomElement($colors);
        $background = fake()->randomElement($backgrounds);

        return fake()->optional(0.8)->passthrough(
            'https://ui-avatars.com/api/?name='.urlencode($name).
            '&size=400&background='.$background.
            '&color='.$color.
            '&bold=true&format=png'
        );
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
