<?php

declare(strict_types=1);

namespace Database\Seeders\Rsi\CommLink;

use App\Models\Rsi\CommLink\Channel\Channel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChannelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        if (Channel::query()->where('name', 'Undefined')->exists()) {
            return;
        }

        DB::table('comm_link_channels')->insert(
            [
                'name' => 'Undefined',
                'slug' => 'undefined',
            ]
        );
    }
}
