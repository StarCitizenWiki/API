<?php declare(strict_types = 1);

use Faker\Generator as Faker;

$factory->define(
    App\Models\Account\Admin\Admin::class,
    function (Faker $faker) {
        return [
            'username' => $faker->userName,
            'password' => bcrypt(config('api.admin_password')),
            'blocked' => false,
            'last_login' => $faker->dateTime,
            'created_at' => Carbon\Carbon::now(),
            'updated_at' => Carbon\Carbon::now(),
        ];
    }
);
