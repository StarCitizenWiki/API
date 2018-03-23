<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

/**
 * Class ShortUrlsTableSeeder
 */
class ShortUrlTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('short_urls')->insert(
            [
                'created_at' => '2014-10-11 11:11:47',
                'hash' => 'scw',
                'url' => 'https://star-citizen.wiki/Star_Citizen_Wiki',
            ]
        );
        DB::table('short_urls')->insert(
            [
                'created_at' => '2015-10-13 03:37:44',
                'hash' => 'CknwK',
                'url' => 'http://starcitizenbase.de/die-ark-starmap/',
            ]
        );
        DB::table('short_urls')->insert(
            [
                'created_at' => '2017-04-03 10:39:13',
                'hash' => '1mRyP',
                'url' => 'http://starcitizenbase.de/angrybot-deutsche-uebersetzung-des-issue-councils-samt-leitfaden/',
            ]
        );
        DB::table('short_urls')->insert(
            [
                'created_at' => '2015-04-23 02:53:21',
                'hash' => 'PO2Wo',
                'url' => 'http://hunter.thecomic.ninja/?comic=issue-1-cover',
            ]
        );
    }
}
