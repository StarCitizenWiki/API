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
                'blocked' => false,
            ]
        );
        DB::table('admin_admin_group')->insert(
            [
                'admin_id' => 1,
                'admin_group_id' => 4,
            ]
        );
        DB::table('admin_admin_group')->insert(
            [
                'admin_id' => 1,
                'admin_group_id' => 5,
            ]
        );

        DB::table('admins')->insert(
            [
                'username' => 'Michael_Corleone',
                'blocked' => false,
            ]
        );
        DB::table('admin_admin_group')->insert(
            [
                'admin_id' => 2,
                'admin_group_id' => 4,
            ]
        );


        DB::table('admins')->insert(
            [
                'username' => 'Keonie',
                'blocked' => false,
            ]
        );
        DB::table('admin_admin_group')->insert(
            [
                'admin_id' => 3,
                'admin_group_id' => 2,
            ]
        );
    }
}
