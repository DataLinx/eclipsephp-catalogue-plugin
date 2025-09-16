<?php

namespace Eclipse\Catalogue\Seeders;

use Eclipse\Catalogue\Models\TaxClass;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Seeder;

class TaxClassSeeder extends Seeder
{
    public function run(): void
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $tenantModel = config('eclipse-catalogue.tenancy.model');

        if ($tenantFK && $tenantModel && class_exists($tenantModel)) {
            $tenants = $tenantModel::all();
            foreach ($tenants as $tenant) {
                EloquentModel::withoutEvents(function () use ($tenant, $tenantFK) {
                    $default = TaxClass::updateOrCreate(
                        ['name' => '22%', $tenantFK => $tenant->id],
                        [
                            'description' => 'Standard VAT rate',
                            'rate' => 22.00,
                            'is_default' => true,
                        ]
                    );

                    // Ensure only one default per tenant
                    TaxClass::where($tenantFK, $tenant->id)
                        ->where('id', '!=', $default->id)
                        ->update(['is_default' => false]);

                    TaxClass::updateOrCreate(
                        ['name' => '9.5%', $tenantFK => $tenant->id],
                        [
                            'description' => 'Reduced VAT rate',
                            'rate' => 9.50,
                            'is_default' => false,
                        ]
                    );
                });
            }
        } else {
            // Single-tenant / no tenancy
            EloquentModel::withoutEvents(function () {
                $default = TaxClass::updateOrCreate(
                    ['name' => '22%'],
                    [
                        'description' => 'Standard VAT rate',
                        'rate' => 22.00,
                        'is_default' => true,
                    ]
                );

                TaxClass::where('id', '!=', $default->id)->update(['is_default' => false]);

                TaxClass::updateOrCreate(
                    ['name' => '9.5%'],
                    [
                        'description' => 'Reduced VAT rate',
                        'rate' => 9.50,
                        'is_default' => false,
                    ]
                );
            });
        }
    }
}
