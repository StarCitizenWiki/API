<?php

declare(strict_types=1);

namespace Database\Seeders\StarCitizen;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Class StatsTableSeeder
 */
class StatTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::unprepared(
            file_get_contents(database_path('dumps/crowdfundstats/crowdfundstats_2012-11-13_-_2018-03-16.sql'))
        );
        DB::unprepared(
            file_get_contents(database_path('dumps/crowdfundstats/crowdfundstats_2018-03-17_-_2018-09-24.sql'))
        );
        DB::unprepared(
            file_get_contents(database_path('dumps/crowdfundstats/crowdfundstats_2018-03-25_-_2018-09-26.sql'))
        );
    }
}
