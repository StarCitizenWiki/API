<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

/**
 * Class UsersTableSeeder
 */
class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = \Carbon\Carbon::now();

        DB::table('users')->insert(
            [
                'name'                => 'Star Citizen Wiki',
                'email'               => 'info@star-citizen.wiki',
                'api_token'           => 'LdDZZynGTIwn39wRK2ZF2iZtWLPJT9hZAjndvAwG8QM7boKmyxPOmLtXqHl6',
                'password'            => bcrypt('starcitizenwiki'),
                'requests_per_minute' => 60,
                'state'               => \App\Models\Account\User\User::STATE_UNTHROTTLED,
                'last_login'          => $now,
                'created_at'          => $now,
            ]
        );

        if (App::environment() === 'local' || App::environment() === 'testing') {
            DB::table('users')->insert(
                [
                    'name'                => 'Whitelisted',
                    'email'               => 'whitelisted@star-citizen.wiki',
                    'api_token'           => str_random(60),
                    'password'            => bcrypt('starcitizenwiki'),
                    'requests_per_minute' => 60,
                    'state'               => \App\Models\Account\User\User::STATE_UNTHROTTLED,
                    'last_login'          => $now,
                    'created_at'          => $now,
                ]
            );

            DB::table('users')->insert(
                [
                    'name'                => 'Blacklisted',
                    'email'               => 'blacklisted@star-citizen.wiki',
                    'api_token'           => str_random(60),
                    'password'            => bcrypt('starcitizenwiki'),
                    'requests_per_minute' => 60,
                    'state'               => \App\Models\Account\User\User::STATE_BLOCKED,
                    'last_login'          => $now,
                    'created_at'          => $now,
                ]
            );

            DB::table('users')->insert(
                [
                    'name'                => 'Normal',
                    'email'               => 'normal@star-citizen.wiki',
                    'api_token'           => str_random(60),
                    'password'            => bcrypt('starcitizenwiki'),
                    'requests_per_minute' => 60,
                    'state'               => \App\Models\Account\User\User::STATE_DEFAULT,
                    'last_login'          => $now,
                    'created_at'          => $now,
                ]
            );
        }
    }
}
