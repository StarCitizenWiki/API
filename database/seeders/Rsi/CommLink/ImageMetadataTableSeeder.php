<?php

declare(strict_types=1);

namespace Database\Seeders\Rsi\CommLink;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImageMetadataTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::unprepared(file_get_contents(database_path('dumps/images/metadata_1_-_24636.sql')));
    }
}
