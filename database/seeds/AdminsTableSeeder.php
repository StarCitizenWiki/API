<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

/**
 * Class AdminsTableSeeder
 */
class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admins')->insert(
            [
                'username' => 'FoXFTW',
                'blocked'  => false,
            ]
        );
        DB::table('wiki_group_admin')->insert(
            [
                'admin_id'      => 1,
                'wiki_group_id' => 4,
            ]
        );
        DB::table('wiki_group_admin')->insert(
            [
                'admin_id'      => 1,
                'wiki_group_id' => 5,
            ]
        );
    }
}
