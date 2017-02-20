<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Star Citizen Wiki',
            'email' => 'info@star-citizen.wiki',
            'api_token' => 'LdDZZynGTIwn39wRK2ZF2iZtWLPJT9hZAjndvAwG8QM7boKmyxPOmLtXqHl6',
            'password' => bcrypt('starcitizenwiki'),
            'requests_per_minute' => 60,
            'whitelisted' => true,
            'blacklisted' => false,
            'last_login' => date('Y-m-d H:i:s'),
        ]);
    }
}
