<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(
    App\Models\User::class,
    function (Faker\Generator $faker) {
        static $password;

        return [
            'name'                       => $faker->name,
            'email'                      => $faker->unique()->safeEmail,
            'api_token'                  => str_random(60),
            'password'                   => $password ?: $password = bcrypt('secret'),
            'requests_per_minute'        => 60,
            'whitelisted'                => false,
            'blacklisted'                => false,
            'receive_notification_level' => 1,
            'notes'                      => '',
            'last_login'                 => date('Y-m-d H:i:s'),
            'created_at'                 => \Carbon\Carbon::now(),
            'api_token_last_used'        => \Carbon\Carbon::now(),
            'remember_token'             => str_random(10),
        ];
    }
);
