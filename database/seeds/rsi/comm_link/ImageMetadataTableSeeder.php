<?php declare(strict_types=1);

use Illuminate\Database\Seeder;

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
