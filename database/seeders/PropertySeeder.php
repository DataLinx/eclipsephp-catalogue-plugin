<?php

namespace Eclipse\Catalogue\Seeders;

use Eclipse\Catalogue\Enums\PropertyInputType;
use Eclipse\Catalogue\Enums\PropertyType;
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
            'name' => ['en' => 'Brand', 'sl' => 'Znamka'],
            'description' => ['en' => 'Product brand or manufacturer', 'sl' => 'Znamka ali proizvajalec izdelka'],
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
                'value' => ['en' => $brand, 'sl' => $brand],
                'sort' => $index * 10,
            ]);
        }

        // Create Color property (global)
        $colorProperty = Property::create([
            'code' => 'color',
            'name' => ['en' => 'Color', 'sl' => 'Barva'],
            'description' => ['en' => 'Product color', 'sl' => 'Barva izdelka'],
            'is_active' => true,
            'is_global' => true,
            'max_values' => 3, // Allow multiple colors
            'enable_sorting' => true,
            'is_filter' => true,
        ]);

        // Create color values
        $colors = [
            ['en' => 'Red', 'sl' => 'Rdeča'],
            ['en' => 'Blue', 'sl' => 'Modra'],
            ['en' => 'Green', 'sl' => 'Zelena'],
            ['en' => 'Black', 'sl' => 'Črna'],
            ['en' => 'White', 'sl' => 'Bela'],
            ['en' => 'Yellow', 'sl' => 'Rumena'],
            ['en' => 'Purple', 'sl' => 'Vijolična'],
            ['en' => 'Orange', 'sl' => 'Oranžna'],
        ];
        foreach ($colors as $index => $color) {
            PropertyValue::create([
                'property_id' => $colorProperty->id,
                'value' => $color,
                'sort' => $index * 10,
            ]);
        }

        // Create Size property (for clothing type only)
        $sizeProperty = Property::create([
            'code' => 'size',
            'name' => ['en' => 'Size', 'sl' => 'Velikost'],
            'description' => ['en' => 'Clothing size', 'sl' => 'Velikost oblačil'],
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
        $materials = [
            ['en' => 'Cotton', 'sl' => 'Bombaž'],
            ['en' => 'Polyester', 'sl' => 'Poliester'],
            ['en' => 'Wool', 'sl' => 'Volna'],
            ['en' => 'Silk', 'sl' => 'Svila'],
            ['en' => 'Leather', 'sl' => 'Usnje'],
            ['en' => 'Plastic', 'sl' => 'Plastika'],
            ['en' => 'Metal', 'sl' => 'Kovina'],
            ['en' => 'Wood', 'sl' => 'Les'],
        ];
        foreach ($materials as $index => $material) {
            PropertyValue::create([
                'property_id' => $materialProperty->id,
                'value' => $material,
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

        // Add Unit of measure as list property (global)
        $uomProperty = Property::create([
            'code' => 'unit_of_measure',
            'name' => ['en' => 'Unit of measure', 'sl' => 'Merska enota'],
            'description' => ['en' => 'Unit of measure for product', 'sl' => 'Merska enota za izdelek'],
            'is_active' => true,
            'is_global' => true,
            'max_values' => 1,
            'enable_sorting' => false,
            'is_filter' => true,
            'type' => PropertyType::LIST->value,
        ]);

        $uomValues = [
            ['en' => 'pcs', 'sl' => 'kos'],
            ['en' => 'set', 'sl' => 'set'],
            ['en' => 'pair', 'sl' => 'par'],
        ];
        foreach ($uomValues as $i => $unitValue) {
            PropertyValue::create([
                'property_id' => $uomProperty->id,
                'value' => $unitValue,
                'sort' => $i * 10,
            ]);
        }

        // Add realistic custom properties (global, custom types)
        Property::create([
            'code' => 'material_details',
            'name' => ['en' => 'Material details', 'sl' => 'Podrobnosti o materialih'],
            'description' => ['en' => 'Details about materials used', 'sl' => 'Podrobnosti o uporabljenih materialih'],
            'is_active' => true,
            'is_global' => true,
            'max_values' => 1,
            'enable_sorting' => false,
            'is_filter' => false,
            'type' => PropertyType::CUSTOM->value,
            'input_type' => PropertyInputType::TEXT->value,
            'is_multilang' => true,
        ]);

        Property::create([
            'code' => 'sku_notes',
            'name' => ['en' => 'SKU notes', 'sl' => 'Opombe SKU'],
            'description' => ['en' => 'Internal notes for SKU', 'sl' => 'Interne opombe za SKU'],
            'is_active' => true,
            'is_global' => true,
            'max_values' => 1,
            'enable_sorting' => false,
            'is_filter' => false,
            'type' => PropertyType::CUSTOM->value,
            'input_type' => PropertyInputType::STRING->value,
            'is_multilang' => false,
        ]);

        Property::create([
            'code' => 'release_date',
            'name' => ['en' => 'Release date', 'sl' => 'Datum izida'],
            'description' => ['en' => 'Product release date', 'sl' => 'Datum izida izdelka'],
            'is_active' => true,
            'is_global' => true,
            'max_values' => 1,
            'enable_sorting' => false,
            'is_filter' => true,
            'type' => PropertyType::CUSTOM->value,
            'input_type' => PropertyInputType::DATE->value,
            'is_multilang' => false,
        ]);

        Property::create([
            'code' => 'dimensions',
            'name' => ['en' => 'Dimensions', 'sl' => 'Dimenzije'],
            'description' => ['en' => 'Approximate dimensions', 'sl' => 'Približne dimenzije'],
            'is_active' => true,
            'is_global' => true,
            'max_values' => 1,
            'enable_sorting' => false,
            'is_filter' => false,
            'type' => PropertyType::CUSTOM->value,
            'input_type' => PropertyInputType::DECIMAL->value,
            'is_multilang' => false,
        ]);

        Property::create([
            'code' => 'tech_sheet',
            'name' => ['en' => 'Tech sheet', 'sl' => 'Tehnični list'],
            'description' => ['en' => 'Technical sheet file', 'sl' => 'Datoteka tehničnega lista'],
            'is_active' => true,
            'is_global' => true,
            'max_values' => 1,
            'enable_sorting' => false,
            'is_filter' => false,
            'type' => PropertyType::CUSTOM->value,
            'input_type' => PropertyInputType::FILE->value,
            'is_multilang' => false,
        ]);
    }
}
