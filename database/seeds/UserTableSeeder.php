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
        DB::table('users')->insert(
            [
                'name'                => 'Star Citizen Wiki',
                'email'               => 'info@star-citizen.wiki',
                'api_token'           => 'LdDZZynGTIwn39wRK2ZF2iZtWLPJT9hZAjndvAwG8QM7boKmyxPOmLtXqHl6',
                'password'            => bcrypt('starcitizenwiki'),
                'requests_per_minute' => 60,
                'state'               => \App\Models\User::STATE_WHITELISTED,
                'last_login'          => '01.01.1970 00:00:00',
                'created_at'          => \Carbon\Carbon::now(),
            ]
        );

        DB::table('users')->insert(
            [
                'name'                => 'Starcitizen Base',
                'email'               => 'info@starcitizenbase.de',
                'api_token'           => str_random(60),
                'password'            => bcrypt('starcitizenbase'),
                'requests_per_minute' => 10,
                'state'               => \App\Models\User::STATE_DEFAULT,
                'last_login'          => '01.01.1970 00:00:00',
                'created_at'          => \Carbon\Carbon::now(),
            ]
        );

        if (App::environment() === 'local') {
            DB::table('users')->insert(
                [
                    'name'                => 'Whitelisted',
                    'email'               => 'whitelisted@star-citizen.wiki',
                    'api_token'           => str_random(60),
                    'password'            => bcrypt('starcitizenwiki'),
                    'requests_per_minute' => 60,
                    'state'               => \App\Models\User::STATE_WHITELISTED,
                    'last_login'          => '01.01.1970 00:00:00',
                    'created_at'          => \Carbon\Carbon::now(),
                ]
            );

            DB::table('users')->insert(
                [
                    'name'                => 'Blacklisted',
                    'email'               => 'blacklisted@star-citizen.wiki',
                    'api_token'           => str_random(60),
                    'password'            => bcrypt('starcitizenwiki'),
                    'requests_per_minute' => 60,
                    'state'               => \App\Models\User::STATE_BLACKLISTED,
                    'last_login'          => '01.01.1970 00:00:00',
                    'created_at'          => \Carbon\Carbon::now(),
                ]
            );

            DB::table('users')->insert(
                [
                    'name'                => 'Normal',
                    'email'               => 'normal@star-citizen.wiki',
                    'api_token'           => str_random(60),
                    'password'            => bcrypt('starcitizenwiki'),
                    'requests_per_minute' => 60,
                    'state'               => \App\Models\User::STATE_DEFAULT,
                    'last_login'          => '01.01.1970 00:00:00',
                    'created_at'          => \Carbon\Carbon::now(),
                ]
            );
        }
    }
}
