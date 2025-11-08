<?php

namespace Workbench\App\Providers;

use Eclipse\Catalogue\Models\Category;
use Eclipse\Catalogue\Models\Group;
use Eclipse\Catalogue\Models\MeasureUnit;
use Eclipse\Catalogue\Models\PriceList;
use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Models\ProductStatus;
use Eclipse\Catalogue\Models\ProductType;
use Eclipse\Catalogue\Models\Property;
use Eclipse\Catalogue\Models\PropertyValue;
use Eclipse\Catalogue\Models\TaxClass;
use Eclipse\Catalogue\Policies\CategoryPolicy;
use Eclipse\Catalogue\Policies\GroupPolicy;
use Eclipse\Catalogue\Policies\MeasureUnitPolicy;
use Eclipse\Catalogue\Policies\PriceListPolicy;
use Eclipse\Catalogue\Policies\ProductPolicy;
use Eclipse\Catalogue\Policies\ProductStatusPolicy;
use Eclipse\Catalogue\Policies\ProductTypePolicy;
use Eclipse\Catalogue\Policies\PropertyPolicy;
use Eclipse\Catalogue\Policies\TaxClassPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Role;
use Workbench\App\Policies\PropertyValuePolicy;
use Workbench\App\Policies\RolePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Category::class => CategoryPolicy::class,
        Group::class => GroupPolicy::class,
        MeasureUnit::class => MeasureUnitPolicy::class,
        PriceList::class => PriceListPolicy::class,
        Product::class => ProductPolicy::class,
        ProductStatus::class => ProductStatusPolicy::class,
        ProductType::class => ProductTypePolicy::class,
        Property::class => PropertyPolicy::class,
        PropertyValue::class => PropertyValuePolicy::class,
        TaxClass::class => TaxClassPolicy::class,
        Role::class => RolePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
