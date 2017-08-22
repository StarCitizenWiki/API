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
        $this->call(UsersTableSeeder::class);
        $this->call(ShortUrlWhitelistsTableSeeder::class);
        $this->call(ShortURLsTableSeeder::class);
        $this->call(AdminsTableSeeder::class);
        $this->call(WikiGroupsSeeder::class);
        if (App::environment() === 'local') {
            $this->call(NotificationsTableSeeder::class);
        }
    }
}
