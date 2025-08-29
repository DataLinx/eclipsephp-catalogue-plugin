<?php

namespace Eclipse\Catalogue\Seeders;

use Eclipse\Catalogue\Models\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        // Create different groups for each tenant
        $tenantModel = config('eclipse-catalogue.tenancy.model');
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $sites = $tenantModel::all();

        // Define various group options
        $groupOptions = [
            ['code' => 'featured', 'name' => 'Featured Products', 'is_browsable' => true],
            ['code' => 'new-arrivals', 'name' => 'New Arrivals', 'is_browsable' => true],
            ['code' => 'best-sellers', 'name' => 'Best Sellers', 'is_browsable' => true],
            ['code' => 'sale', 'name' => 'Sale Items', 'is_browsable' => false],
            ['code' => 'trending', 'name' => 'Trending Now', 'is_browsable' => true],
            ['code' => 'staff-picks', 'name' => 'Staff Picks', 'is_browsable' => true],
            ['code' => 'seasonal', 'name' => 'Seasonal Collection', 'is_browsable' => true],
            ['code' => 'limited-edition', 'name' => 'Limited Edition', 'is_browsable' => true],
            ['code' => 'clearance', 'name' => 'Clearance Items', 'is_browsable' => false],
            ['code' => 'premium', 'name' => 'Premium Selection', 'is_browsable' => true],
            ['code' => 'eco-friendly', 'name' => 'Eco-Friendly', 'is_browsable' => true],
            ['code' => 'gift-guide', 'name' => 'Gift Guide', 'is_browsable' => true],
        ];

        foreach ($sites as $site) {
            // Randomly select 4-6 groups for each tenant
            $numGroups = rand(4, 6);
            $selectedGroups = collect($groupOptions)->shuffle()->take($numGroups);

            foreach ($selectedGroups as $groupData) {
                Group::create([
                    $tenantFK => $site->id,
                    'code' => $groupData['code'],
                    'name' => $groupData['name'],
                    'is_active' => true,
                    'is_browsable' => $groupData['is_browsable'],
                ]);
            }
        }
    }
}
