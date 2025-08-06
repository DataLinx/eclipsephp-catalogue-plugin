<?php

use Eclipse\Catalogue\Filament\Resources\ProductResource;
use Eclipse\Catalogue\Models\Category;
use Eclipse\Catalogue\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->setUpSuperAdmin();
});

it('can filter products by category in table', function (): void {
    $electronics = Category::factory()->create(['name' => 'Electronics']);
    $books = Category::factory()->create(['name' => 'Books']);

    $laptop = Product::factory()->create([
        'name' => 'Gaming Laptop',
        'category_id' => $electronics->id,
    ]);

    $novel = Product::factory()->create([
        'name' => 'Science Fiction Novel',
        'category_id' => $books->id,
    ]);

    $orphanProduct = Product::factory()->create([
        'name' => 'No Category Product',
        'category_id' => null,
    ]);

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->filterTable('category_id', [$electronics->id])
        ->assertCanSeeTableRecords([$laptop])
        ->assertCanNotSeeTableRecords([$novel, $orphanProduct]);
});
