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
                'language_id' => 1,
                'vehicle_size_id' => 1,
                'size' => 'undefined',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
