<?php

declare(strict_types=1);

namespace Database\Seeders\Rsi\CommLink;

use App\Models\Rsi\CommLink\Series\Series;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        if (DB::table('comm_link_series')->where('name', 'None')->exists()) {
            return;
        }

        DB::table('comm_link_series')->insert(
            [
                'name' => 'None',
                'slug' => 'undefined',
            ]
        );
    }
}
