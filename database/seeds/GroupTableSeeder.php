<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

/**
 * Class GroupsTableSeeder
 */
class GroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('groups')->insert(
            [
                'name'             => 'user',
                'permission_level' => 0,
            ]
        );
        DB::table('groups')->insert(
            [
                'name'             => 'mitarbeiter',
                'permission_level' => 1,
            ]
        );
        DB::table('groups')->insert(
            [
                'name'             => 'sichter',
                'permission_level' => 2,
            ]
        );
        DB::table('groups')->insert(
            [
                'name'             => 'sysop',
                'permission_level' => 3,
            ]
        );
        DB::table('groups')->insert(
            [
                'name'             => 'bureaucrat',
                'permission_level' => 4,
            ]
        );
    }
}
