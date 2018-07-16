<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

class ProductionStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = \Carbon\Carbon::now();

        DB::table('production_statuses')->insert(
            [
                'id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('production_status_translations')->insert(
            [
                'language_id' => 1,
                'production_status_id' => 1,
                'status' => 'undefined',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
