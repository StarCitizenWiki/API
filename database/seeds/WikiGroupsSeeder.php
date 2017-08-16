<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

/**
 * Class WikiGroupsSeeder
 */
class WikiGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('wiki_groups')->insert(
            [
                'name' => 'user',
            ]
        );
        DB::table('wiki_groups')->insert(
            [
                'name' => 'mitarbeiter',
            ]
        );
        DB::table('wiki_groups')->insert(
            [
                'name' => 'sichter',
            ]
        );
        DB::table('wiki_groups')->insert(
            [
                'name' => 'sysop',
            ]
        );
        DB::table('wiki_groups')->insert(
            [
                'name' => 'bureaucrat',
            ]
        );
    }
}
