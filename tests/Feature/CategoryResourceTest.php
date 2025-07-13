<?php

use Eclipse\Catalogue\Filament\Resources\CategoryResource\Pages\CreateCategory;
use Eclipse\Catalogue\Models\Category;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Workbench\App\Models\Site;

beforeEach(function (): void {
    $this->site = Site::factory()->create();
    $this->otherSite = Site::factory()->create();

    $this->setUpSuperAdmin();

    Filament::setTenant($this->site);
});

it('can create a category', function () {
    $categoryData = [
        'name' => 'Test Category',
        'sef_key' => 'test-category',
        'short_desc' => 'Test description',
        'description' => 'Test detailed description',
        'is_active' => true,
        'recursive_browsing' => false,
        'code' => 'TEST_CAT',
        'site_id' => $this->site->id,
    ];

    Livewire::test(CreateCategory::class)
        ->fillForm($categoryData)
        ->call('create')
        ->assertHasNoFormErrors();

    $createdCategory = Category::latest()->first();
    expect($createdCategory->name)->not()->toBeNull();
});

// it('can read categories in table', function () {
//     $category = Category::create([
//         'name' => 'Test Category',
//         'sef_key' => 'test-category',
//         'short_desc' => 'Test description',
//         'description' => 'Test detailed description',
//         'is_active' => true,
//         'recursive_browsing' => false,
//         'code' => 'TEST_CAT',
//         'site_id' => $this->site->id,
//     ]);

//     Livewire::test(\Eclipse\Catalogue\Filament\Resources\CategoryResource\Pages\ListCategories::class)
//         ->assertCanSeeTableRecords([$category]);
// });
