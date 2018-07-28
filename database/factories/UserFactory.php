<?php declare(strict_types = 1);

use Faker\Generator as Faker;

$factory->define(
    App\Models\Account\User::class,
    function (Faker $faker) {
        return [
            'name' => $faker->name,
            'email' => $faker->unique()->safeEmail,
            'api_token' => str_random(60),
            'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
            'remember_token' => str_random(10),
            'requests_per_minute' => 60,
            'state' => \App\Models\Account\User::STATE_DEFAULT,
            'receive_notification_level' => 1,
            'notes' => '',
            'last_login' => date('Y-m-d H:i:s'),
            'created_at' => \Carbon\Carbon::now(),
            'api_token_last_used' => \Carbon\Carbon::now(),
        ];
    }
);
