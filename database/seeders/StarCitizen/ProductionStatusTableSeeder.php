<?php

declare(strict_types=1);

namespace Database\Seeders\StarCitizen;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductionStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('shipmatrix_production_statuses')->where('id', 1)->exists()) {
            return;
        }

        DB::table('shipmatrix_production_statuses')->insert(
            [
                'id' => 1,
                'slug' => 'undefined',
                'translation' => json_encode([
                    'en' => 'Undefined',
                    'de' => 'Undefiniert',
                ], JSON_THROW_ON_ERROR),
            ]
        );
    }
}
