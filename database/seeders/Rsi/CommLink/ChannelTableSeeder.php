<?php

declare(strict_types=1);

namespace Database\Seeders\Rsi\CommLink;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChannelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('comm_link_channels')->insert(
            [
                'name' => 'Undefined',
                'slug' => 'undefined',
            ]
        );
    }
}
