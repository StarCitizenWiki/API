<?php

declare(strict_types=1);

namespace Database\Seeders\StarCitizen;

use App\Models\StarCitizen\ProductionStatus\ProductionStatus;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductionStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
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
    }
}
