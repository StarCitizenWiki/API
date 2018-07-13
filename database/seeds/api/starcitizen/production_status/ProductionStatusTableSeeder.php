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
        DB::table('production_statuses_translations')->insert(
            [
                'language_id' => 1,
                'production_status_id' => 1,
                'status' => 'flight-ready',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('production_statuses')->insert(
            [
                'id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('production_statuses_translations')->insert(
            [
                'language_id' => 1,
                'production_status_id' => 2,
                'status' => 'announced',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('production_statuses')->insert(
            [
                'id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('production_statuses_translations')->insert(
            [
                'language_id' => 1,
                'production_status_id' => 3,
                'status' => 'ready',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('production_statuses')->insert(
            [
                'id' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('production_statuses_translations')->insert(
            [
                'language_id' => 1,
                'production_status_id' => 4,
                'status' => 'in-concept',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('production_statuses')->insert(
            [
                'id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('production_statuses_translations')->insert(
            [
                'language_id' => 1,
                'production_status_id' => 5,
                'status' => 'in-production',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('production_statuses')->insert(
            [
                'id' => 6,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('production_statuses_translations')->insert(
            [
                'language_id' => 1,
                'production_status_id' => 6,
                'status' => 'undefined',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
