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
            'url' => 'robertsspaceindustries.com'
        ]);
        DB::table('short_url_whitelists')->insert([
            'url' => 'forums.robertsspaceindustries.com'
        ]);
        DB::table('short_url_whitelists')->insert([
            'url' => 'stargov.de',
            'internal' => true
        ]);
        DB::table('short_url_whitelists')->insert([
            'url' => 'star-citizen.wiki'
        ]);
        DB::table('short_url_whitelists')->insert([
            'url' => 'facebook.com'
        ]);
        DB::table('short_url_whitelists')->insert([
            'url' => 'forum.crashcorps.de'
        ]);
        DB::table('short_url_whitelists')->insert([
            'url' => 'starcitizenbase.de'
        ]);
        DB::table('short_url_whitelists')->insert([
            'url' => 'youtube.com'
        ]);
        DB::table('short_url_whitelists')->insert([
            'url' => 'youtu.be',
            'internal' => true,
        ]);
    }
}
