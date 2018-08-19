<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

class VehicleSizeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = \Carbon\Carbon::now();

        DB::table('vehicle_sizes')->insert(
            [
                'id' => 1,
            ]
        );
        DB::table('vehicle_size_translations')->insert(
            [
                'locale_code' => 'en_EN',
                'vehicle_size_id' => 1,
                'translation' => 'undefined',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('vehicle_size_translations')->insert(
            [
                'locale_code' => 'de_DE',
                'vehicle_size_id' => 1,
                'translation' => 'Undefiniert',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
