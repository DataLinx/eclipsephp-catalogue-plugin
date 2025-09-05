<?php

namespace Eclipse\Catalogue\Seeders;

use Eclipse\Catalogue\Models\ProductType;
use Eclipse\Catalogue\Models\Property;
use Eclipse\Catalogue\Models\PropertyValue;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        // Create Brand property (global)
        $brandProperty = Property::create([
            'code' => 'brand',
            'name' => ['en' => 'Brand'],
            'description' => ['en' => 'Product brand or manufacturer'],
            'internal_name' => 'Brand/Manufacturer',
            'is_active' => true,
            'is_global' => true,
            'max_values' => 1,
            'enable_sorting' => false,
            'is_filter' => true,
        ]);

        // Create brand values
        $brands = ['Nike', 'Adidas', 'Apple', 'Samsung', 'Sony'];
        foreach ($brands as $index => $brand) {
            PropertyValue::create([
                'property_id' => $brandProperty->id,
                'value' => ['en' => $brand],
                'sort' => $index * 10,
            ]);
        }

        // Create Color property (global)
        $colorProperty = Property::create([
            'code' => 'color',
            'name' => ['en' => 'Color'],
            'description' => ['en' => 'Product color'],
            'is_active' => true,
            'is_global' => true,
            'type' => \Eclipse\Catalogue\Enums\PropertyType::COLOR->value,
            'max_values' => 3, // Allow multiple colors
            'enable_sorting' => true,
            'is_filter' => true,
        ]);

        // Create color values
        $colors = ['Red', 'Blue', 'Green', 'Black', 'White', 'Yellow', 'Purple', 'Orange'];
        $hexMap = [
            'Red' => '#ff0000',
            'Blue' => '#0000ff',
            'Green' => '#00ff00',
            'Black' => '#000000',
            'White' => '#ffffff',
            'Yellow' => '#ffff00',
            'Purple' => '#800080',
            'Orange' => '#ffa500',
        ];
        foreach ($colors as $index => $color) {
            $pv = PropertyValue::create([
                'property_id' => $colorProperty->id,
                'value' => ['en' => $color],
                'sort' => $index * 10,
            ]);
            $hex = $hexMap[$color] ?? null;
            if ($hex) {
                $pv->color = json_encode(['type' => 's', 'color' => $hex]);
                $pv->save();
            }
        }

        // Create Size property (for clothing type only)
        $sizeProperty = Property::create([
            'code' => 'size',
            'name' => ['en' => 'Size'],
            'description' => ['en' => 'Clothing size'],
            'is_active' => true,
            'is_global' => false,
            'max_values' => 1,
            'enable_sorting' => true,
            'is_filter' => true,
        ]);

        // Create size values
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        foreach ($sizes as $index => $size) {
            PropertyValue::create([
                'property_id' => $sizeProperty->id,
                'value' => ['en' => $size],
                'sort' => $index * 10,
            ]);
        }

        // Create Material property
        $materialProperty = Property::create([
            'code' => 'material',
            'name' => ['en' => 'Material'],
            'description' => ['en' => 'Product material composition'],
            'is_active' => true,
            'is_global' => false,
            'max_values' => 2, // Allow multiple materials
            'enable_sorting' => false,
            'is_filter' => true,
        ]);

        // Create material values
        $materials = ['Cotton', 'Polyester', 'Wool', 'Silk', 'Leather', 'Plastic', 'Metal', 'Wood'];
        foreach ($materials as $index => $material) {
            PropertyValue::create([
                'property_id' => $materialProperty->id,
                'value' => ['en' => $material],
                'sort' => $index * 10,
            ]);
        }

        // If there are product types, assign non-global properties to some of them
        $productTypes = ProductType::all();
        if ($productTypes->isNotEmpty()) {
            // Assign size to first product type (assuming it's clothing)
            if ($productTypes->count() > 0) {
                $productTypes->first()->properties()->attach($sizeProperty->id, ['sort' => 10]);
            }

            // Assign material to first two product types
            foreach ($productTypes->take(2) as $index => $productType) {
                $productType->properties()->attach($materialProperty->id, ['sort' => 20]);
            }
        }
    }
}
