<?php

declare(strict_types=1);

namespace Database\Seeders\Api\Starcitizen\Vehicle;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = \Carbon\Carbon::now();

        DB::table('vehicle_types')->insert(
            [
                'id' => 1,
                'slug' => 'undefined',
            ]
        );
        DB::table('vehicle_type_translations')->insert(
            [
                'locale_code' => 'en_EN',
                'type_id' => 1,
                'translation' => 'undefined',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('vehicle_type_translations')->insert(
            [
                'locale_code' => 'de_DE',
                'type_id' => 1,
                'translation' => 'Undefiniert',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
