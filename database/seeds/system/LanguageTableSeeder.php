<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

class LanguageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = \Carbon\Carbon::now();

        DB::table('languages')->insert(
            [
                'id' => 1,
                'locale_code' => 'en_EN',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('languages')->insert(
            [
                'id' => 2,
                'locale_code' => 'de_DE',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}