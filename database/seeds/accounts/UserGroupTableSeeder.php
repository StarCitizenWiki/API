<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

/**
 * Class GroupsTableSeeder
 */
class UserGroupTableSeeder extends Seeder
{
    public const ATTR_PERMISSION_LEVEL = 'permission_level';
    public const ATTR_NAME = 'name';
    public const TBL_GROUPS = 'user_groups';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table(self::TBL_GROUPS)->insert(
            [
                self::ATTR_NAME             => 'user',
                self::ATTR_PERMISSION_LEVEL => 0,
            ]
        );
        DB::table(self::TBL_GROUPS)->insert(
            [
                self::ATTR_NAME             => 'mitarbeiter',
                self::ATTR_PERMISSION_LEVEL => 1,
            ]
        );
        DB::table(self::TBL_GROUPS)->insert(
            [
                self::ATTR_NAME => 'sichter',
                self::ATTR_PERMISSION_LEVEL => 2,
            ]
        );
        DB::table(self::TBL_GROUPS)->insert(
            [
                self::ATTR_NAME => 'sysop',
                self::ATTR_PERMISSION_LEVEL => 3,
            ]
        );
        if (DB::table(self::TBL_GROUPS)->where(self::ATTR_NAME, 'bureaucrat')->count() === 0) {
            DB::table(self::TBL_GROUPS)->insert(
                [
                    self::ATTR_NAME => 'bureaucrat',
                    self::ATTR_PERMISSION_LEVEL => 4,
                ]
            );
        }
    }
}
