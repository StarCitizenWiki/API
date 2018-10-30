<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

class SeriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('comm_link_series')->insert(
            [
                'name' => 'None',
                'slug' => 'undefined',
            ]
        );
    }
}
