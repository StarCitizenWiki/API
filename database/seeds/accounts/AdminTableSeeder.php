<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

/**
 * Class AdminsTableSeeder
 */
class AdminTableSeeder extends Seeder
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
        DB::table('admin_admin_groups')->insert(
            [
                'admin_id' => 1,
                'group_id' => 4,
            ]
        );
        DB::table('admin_admin_groups')->insert(
            [
                'admin_id' => 1,
                'group_id' => 5,
            ]
        );
    }
}
