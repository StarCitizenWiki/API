<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

/**
 * Class ShortUrlWhitelistsTableSeeder
 */
class ShortUrlWhitelistTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('short_url_whitelists')->insert(
            [
                'url'        => 'robertsspaceindustries.com',
                'created_at' => '2017-01-01 00:00:00',
            ]
        );
        DB::table('short_url_whitelists')->insert(
            [
                'url'        => 'forums.robertsspaceindustries.com',
                'created_at' => '2017-01-01 00:00:00',
            ]
        );
        DB::table('short_url_whitelists')->insert(
            [
                'url'        => 'star-citizen.wiki',
                'created_at' => '2017-01-01 00:00:00',
            ]
        );
        DB::table('short_url_whitelists')->insert(
            [
                'url'        => 'starcitizenbase.de',
                'created_at' => '2017-01-01 00:00:00',
            ]
        );
    }
}
