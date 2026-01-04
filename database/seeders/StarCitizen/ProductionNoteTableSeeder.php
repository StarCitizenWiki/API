<?php

declare(strict_types=1);

namespace Database\Seeders\StarCitizen;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductionNoteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('shipmatrix_production_notes')->where('id', 1)->exists()) {
            return;
        }

        DB::table('shipmatrix_production_notes')->insert(
            [
                'id' => 1,
                'translation' => json_encode([
                    'en' => 'None',
                    'de' => 'Keine',
                ], JSON_THROW_ON_ERROR),
            ]
        );
    }
}
