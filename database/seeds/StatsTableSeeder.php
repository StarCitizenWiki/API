<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

/**
 * Class StatsTableSeeder
 */
class StatsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::unprepared(file_get_contents(database_path('dumps/crowdfundstats_2012-11-13_-_2018-03-15.sql')));
    }
}