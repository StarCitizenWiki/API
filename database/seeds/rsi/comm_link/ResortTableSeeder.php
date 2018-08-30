<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

class ResortTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('resorts')->insert(
            [
                'name' => 'Undefined',
            ]
        );
    }
}
