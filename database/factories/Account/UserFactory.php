<?php declare(strict_types = 1);

use Faker\Generator as Faker;

$factory->define(
    App\Models\Account\User\User::class,
    function (Faker $faker) {
        static $id = 1;

        return [
            'username' => $faker->userName,
            'email' => $faker->email,
            'blocked' => false,
            'provider' => 'starcitizenwiki',
            'provider_id' => $id++,
            'last_login' => $faker->dateTime,
            'api_token' => str_random(60),
            'created_at' => Carbon\Carbon::now(),
            'updated_at' => Carbon\Carbon::now(),
        ];
    }
);

$factory->state(
    \App\Models\Account\User\User::class,
    'blocked',
    [
        'blocked' => true,
    ]
);

$factory->define(
    App\Models\Account\User\UserGroup::class,
    function (Faker $faker) {
        return [
            'name' => $faker->userName,
            'permission_level' => $faker->numberBetween(0, 4),
        ];
    }
);

$factory->state(
    App\Models\Account\User\UserGroup::class,
    'bureaucrat',
    [
        'name' => 'bureaucrat',
        'permission_level' => 4,
    ]
);

$factory->state(
    App\Models\Account\User\UserGroup::class,
    'sysop',
    [
        'name' => 'sysop',
        'permission_level' => 3,
    ]
);

$factory->state(
    App\Models\Account\User\UserGroup::class,
    'sichter',
    [
        'name' => 'sichter',
        'permission_level' => 2,
    ]
);

$factory->state(
    App\Models\Account\User\UserGroup::class,
    'mitarbeiter',
    [
        'name' => 'mitarbeiter',
        'permission_level' => 1,
    ]
);

$factory->state(
    App\Models\Account\User\UserGroup::class,
    'editor',
    [
        'name' => 'editor',
        'permission_level' => 0,
    ]
);

$factory->state(
    App\Models\Account\User\UserGroup::class,
    'user',
    [
        'name' => 'user',
        'permission_level' => 0,
    ]
);
