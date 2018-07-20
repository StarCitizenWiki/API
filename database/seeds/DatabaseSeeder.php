<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

/**
 * Class DatabaseSeeder
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedSystemTables();
        $this->seedAccountTables();
        $this->seedApiTables();
    }

    /**
     * System Tables
     */
    private function seedSystemTables()
    {
        $this->call(LanguageTableSeeder::class);
    }

    /**
     * Account Tables: Groups, Admin, Users
     */
    private function seedAccountTables()
    {
        $this->call(AdminGroupTableSeeder::class);
        $this->call(AdminTableSeeder::class);
        $this->call(UserTableSeeder::class);
    }

    /**
     * API Tables
     */
    private function seedApiTables()
    {
        /** Stats */
        $this->call(StatTableSeeder::class);

        /** Ships */
        $this->call(ProductionStatusTableSeeder::class);
        $this->call(ProductionNoteTableSeeder::class);

        if (App::environment() === 'local') {
            $this->call(NotificationTableSeeder::class);
        }
    }
}
