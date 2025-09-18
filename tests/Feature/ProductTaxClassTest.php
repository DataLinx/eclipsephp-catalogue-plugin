<?php

use Eclipse\Catalogue\Filament\Resources\ProductResource;
use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Models\ProductData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->setUpSuperAdminAndTenant();
});

it('can set and unset tax class per tenant', function (): void {
    $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
    $tenantModel = config('eclipse-catalogue.tenancy.model');
    $currentTenantId = $tenantModel::first()->id;

    $taxClass = \Eclipse\Catalogue\Models\TaxClass::create([
        'name' => 'Standard',
        'rate' => 22.00,
        'is_default' => false,
        $tenantFK => $currentTenantId,
    ]);

    $product = Product::factory()->create(['name' => 'Test Product']);

    // Set
    ProductData::factory()->create([
        'product_id' => $product->id,
        $tenantFK => $currentTenantId,
        'tax_class_id' => $taxClass->id,
        'is_active' => true,
    ]);

    expect($product->fresh()->currentTenantData()->tax_class_id)->toBe($taxClass->id);

    // Unset
    $product->currentTenantData()->update(['tax_class_id' => null]);
    expect($product->fresh()->currentTenantData()->tax_class_id)->toBeNull();
});

it('table column is hidden by default and shows correct value when enabled', function (): void {
    $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
    $tenantModel = config('eclipse-catalogue.tenancy.model');
    $currentTenantId = $tenantModel::first()->id;

    $taxClass = \Eclipse\Catalogue\Models\TaxClass::create([
        'name' => 'Reduced',
        'rate' => 9.50,
        'is_default' => false,
        $tenantFK => $currentTenantId,
    ]);

    $product = Product::factory()->create(['name' => 'With Tax']);
    ProductData::factory()->create([
        'product_id' => $product->id,
        $tenantFK => $currentTenantId,
        'tax_class_id' => $taxClass->id,
        'is_active' => true,
    ]);

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->assertTableColumnHidden('tax_class')
        ->assertSee($product->name) // sanity
        ->callTableColumnAction('tax_class', 'toggleVisibility')
        ->assertSee('Reduced');
});

it('filter returns correct products within tenant', function (): void {
    $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
    $tenantModel = config('eclipse-catalogue.tenancy.model');
    $currentTenantId = $tenantModel::first()->id;

    $standard = \Eclipse\Catalogue\Models\TaxClass::create([
        'name' => 'Standard',
        'rate' => 22.00,
        'is_default' => false,
        $tenantFK => $currentTenantId,
    ]);
    $reduced = \Eclipse\Catalogue\Models\TaxClass::create([
        'name' => 'Reduced',
        'rate' => 9.50,
        'is_default' => false,
        $tenantFK => $currentTenantId,
    ]);

    $p1 = Product::factory()->create(['name' => 'P1']);
    $p2 = Product::factory()->create(['name' => 'P2']);
    $p3 = Product::factory()->create(['name' => 'P3']);

    ProductData::factory()->create(['product_id' => $p1->id, $tenantFK => $currentTenantId, 'tax_class_id' => $standard->id, 'is_active' => true]);
    ProductData::factory()->create(['product_id' => $p2->id, $tenantFK => $currentTenantId, 'tax_class_id' => $reduced->id, 'is_active' => true]);
    ProductData::factory()->create(['product_id' => $p3->id, $tenantFK => $currentTenantId, 'tax_class_id' => null, 'is_active' => true]);

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->filterTable('tax_class_id', [$standard->id])
        ->assertCanSeeTableRecords([$p1])
        ->assertCanNotSeeTableRecords([$p2, $p3]);
});
