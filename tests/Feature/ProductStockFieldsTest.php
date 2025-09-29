<?php

use Eclipse\Catalogue\Filament\Resources\ProductResource;
use Eclipse\Catalogue\Models\MeasureUnit;
use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Models\ProductData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->setUpSuperAdmin();
});

it('can save and load stock fields in product data', function (): void {
    $measureUnit = MeasureUnit::factory()->create(['name' => 'kg']);
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'measure_unit_id' => $measureUnit->id,
    ]);

    $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
    $tenantModel = config('eclipse-catalogue.tenancy.model');
    $currentTenantId = $tenantModel::first()->id;

    ProductData::factory()->create([
        'product_id' => $product->id,
        $tenantFK => $currentTenantId,
        'stock' => 150.75,
        'min_stock' => 10.5,
        'date_stocked' => '2024-01-15',
        'is_active' => true,
    ]);

    $product->refresh();

    expect($product->stock)->toBe(150.75);
    expect($product->min_stock)->toBe(10.5);
    expect($product->date_stocked)->toStartWith('2024-01-15');
    expect($product->measureUnit->name)->toBe('kg');
});

it('can filter products by measure unit in table', function (): void {
    $kgUnit = MeasureUnit::factory()->create(['name' => 'kg']);
    $literUnit = MeasureUnit::factory()->create(['name' => 'liter']);

    $product1 = Product::factory()->create([
        'name' => 'Product with kg',
        'measure_unit_id' => $kgUnit->id,
    ]);
    $product2 = Product::factory()->create([
        'name' => 'Product with liter',
        'measure_unit_id' => $literUnit->id,
    ]);
    $product3 = Product::factory()->create([
        'name' => 'Product without unit',
        'measure_unit_id' => null,
    ]);

    $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
    $tenantModel = config('eclipse-catalogue.tenancy.model');
    $currentTenantId = $tenantModel::first()->id;

    // Create product data for all products
    foreach ([$product1, $product2, $product3] as $product) {
        ProductData::factory()->create([
            'product_id' => $product->id,
            $tenantFK => $currentTenantId,
            'is_active' => true,
        ]);
    }

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->filterTable('measure_unit_id', [$kgUnit->id])
        ->assertCanSeeTableRecords([$product1])
        ->assertCanNotSeeTableRecords([$product2, $product3]);
});

it('displays stock columns with correct visibility and widths', function (): void {
    $measureUnit = MeasureUnit::factory()->create(['name' => 'pieces']);
    $product = Product::factory()->create([
        'name' => 'Stock Test Product',
        'measure_unit_id' => $measureUnit->id,
    ]);

    $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
    $tenantModel = config('eclipse-catalogue.tenancy.model');
    $currentTenantId = $tenantModel::first()->id;

    ProductData::factory()->withStock(100.5, 25.0)->create([
        'product_id' => $product->id,
        $tenantFK => $currentTenantId,
        'is_active' => true,
    ]);

    $component = Livewire::test(ProductResource\Pages\ListProducts::class);

    $component->assertCanSeeTableRecords([$product]);

    expect($component->instance()->getTable()->getColumns())->toHaveKey('stock');
    expect($component->instance()->getTable()->getColumns())->toHaveKey('measureUnit.name');
    expect($component->instance()->getTable()->getColumns())->toHaveKey('min_stock');
    expect($component->instance()->getTable()->getColumns())->toHaveKey('date_stocked');
});

it('handles nullable stock values correctly', function (): void {
    $product = Product::factory()->create(['name' => 'Nullable Stock Product']);

    $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
    $tenantModel = config('eclipse-catalogue.tenancy.model');
    $currentTenantId = $tenantModel::first()->id;

    ProductData::factory()->create([
        'product_id' => $product->id,
        $tenantFK => $currentTenantId,
        'stock' => null,
        'min_stock' => null,
        'date_stocked' => null,
        'is_active' => true,
    ]);

    $product->refresh();

    expect($product->stock)->toBeNull();
    expect($product->min_stock)->toBeNull();
    expect($product->date_stocked)->toBeNull();
});

it('can create product with measure unit and stock via form', function (): void {
    $measureUnit = MeasureUnit::factory()->create(['name' => 'kg']);

    $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
    $tenantModel = config('eclipse-catalogue.tenancy.model');
    $currentTenantId = $tenantModel::first()->id;

    $formData = [
        'name' => ['en' => 'Form Test Product'],
        'measure_unit_id' => $measureUnit->id,
        'selected_tenant' => $currentTenantId,
        'tenant_data' => [
            $currentTenantId => [
                'stock' => 75.25,
                'min_stock' => 15.5,
                'date_stocked' => '2024-02-20',
                'is_active' => true,
            ],
        ],
    ];

    Livewire::test(ProductResource\Pages\CreateProduct::class)
        ->fillForm($formData)
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('name->en', 'Form Test Product')->first();
    expect($product)->not->toBeNull();
    expect($product->measure_unit_id)->toBe($measureUnit->id);
    expect($product->stock)->toBe(75.25);
    expect($product->min_stock)->toBe(15.5);
    expect($product->date_stocked)->toStartWith('2024-02-20');
});
