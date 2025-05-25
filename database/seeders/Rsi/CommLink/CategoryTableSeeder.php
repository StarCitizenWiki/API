<?php

declare(strict_types=1);

namespace Database\Seeders\Rsi\CommLink;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('comm_link_categories')->where('name', 'Undefined')->exists()) {
            return;
        }

        DB::table('comm_link_categories')->insert(
            [
                'name' => 'Undefined',
                'slug' => 'undefined',
            ]
        );
    }
}
