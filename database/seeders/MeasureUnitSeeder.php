<?php

namespace Eclipse\Catalogue\Seeders;

use Eclipse\Catalogue\Models\MeasureUnit;
use Illuminate\Database\Seeder;

class MeasureUnitSeeder extends Seeder
{
    public function run(): void
    {
        // Create pcs / kos as default
        $pcs = MeasureUnit::updateOrCreate(
            ['name' => 'pcs / kos'],
            ['is_default' => true]
        );

        // Ensure only one default remains true
        MeasureUnit::where('id', '!=', $pcs->id)->update(['is_default' => false]);

        // Create set / set
        MeasureUnit::updateOrCreate(
            ['name' => 'set / set'],
            ['is_default' => false]
        );

        // Create pair / par
        MeasureUnit::updateOrCreate(
            ['name' => 'pair / par'],
            ['is_default' => false]
        );
    }
}
