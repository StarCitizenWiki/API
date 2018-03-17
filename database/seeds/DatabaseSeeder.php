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
        $this->call(UserTableSeeder::class);
        $this->call(ShortUrlWhitelistTableSeeder::class);
        $this->call(ShortUrlTableSeeder::class);
        $this->call(AdminTableSeeder::class);
        $this->call(GroupTableSeeder::class);
        $this->call(StatsTableSeeder::class);
        if (App::environment() === 'local') {
            $this->call(NotificationTableSeeder::class);
        }
    }
}
