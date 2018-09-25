<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

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
            ]
        );
        DB::table('vehicle_type_translations')->insert(
            [
                'locale_code' => 'en_EN',
                'vehicle_type_id' => 1,
                'translation' => 'undefined',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('vehicle_type_translations')->insert(
            [
                'locale_code' => 'de_DE',
                'vehicle_type_id' => 1,
                'translation' => 'Undefiniert',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
