<?php

use Illuminate\Database\Seeder;

class ShortUrlWhitelistsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('short_url_whitelists')->insert([
            'url' => 'stargov.de'
        ]);

    }
}
