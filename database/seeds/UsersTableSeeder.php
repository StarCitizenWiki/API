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
            'api_token' => str_random(60),
        ]);
    }
}
