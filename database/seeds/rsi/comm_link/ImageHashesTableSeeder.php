<?php declare(strict_types=1);

use Illuminate\Database\Seeder;

class ImageHashesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::unprepared(file_get_contents(database_path('dumps/images/hashes_1_-_24636.sql')));
    }
}
