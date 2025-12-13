<?php

declare(strict_types=1);

namespace Database\Seeders\StarCitizen;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductionStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('production_statuses')->where('id', 1)->exists()) {
            return;
        }

        $now = Carbon::now();

        DB::table('production_statuses')->insert(
            [
                'id' => 1,
                'slug' => 'undefined',
            ]
        );
        DB::table('production_status_translations')->insert(
            [
                'locale_code' => 'en_EN',
                'production_status_id' => 1,
                'translation' => 'undefined',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('production_status_translations')->insert(
            [
                'locale_code' => 'de_DE',
                'production_status_id' => 1,
                'translation' => 'Undefiniert',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // Reset the auto-increment sequence to prevent conflicts with parallel jobs
        DB::statement("SELECT setval('production_statuses_id_seq', (SELECT MAX(id) FROM production_statuses))");
    }
}
