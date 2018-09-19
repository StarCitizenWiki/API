<?php declare(strict_types = 1);

use Faker\Generator as Faker;

$factory->define(
    App\Models\Account\Admin\Admin::class,
    function (Faker $faker) {
        static $id = 1;

        return [
            'username' => $faker->userName,
            'email' => $faker->email,
            'blocked' => false,
            'provider' => 'starcitizenwiki',
            'provider_id' => $id++,
            'last_login' => $faker->dateTime,
            'created_at' => Carbon\Carbon::now(),
            'updated_at' => Carbon\Carbon::now(),
        ];
    }
);

$factory->state(
    \App\Models\Account\Admin\Admin::class,
    'blocked',
    [
        'blocked' => true,
    ]
);

$factory->define(
    App\Models\Account\Admin\AdminGroup::class,
    function (Faker $faker) {
        return [
            'name' => $faker->userName,
            'permission_level' => $faker->numberBetween(0, 4),
        ];
    }
);

$factory->state(
    App\Models\Account\Admin\AdminGroup::class,
    'bureaucrat',
    [
        'name' => 'bureaucrat',
        'permission_level' => 4,
    ]
);

$factory->state(
    App\Models\Account\Admin\AdminGroup::class,
    'sysop',
    [
        'name' => 'sysop',
        'permission_level' => 3,
    ]
);

$factory->state(
    App\Models\Account\Admin\AdminGroup::class,
    'sichter',
    [
        'name' => 'sichter',
        'permission_level' => 2,
    ]
);

$factory->state(
    App\Models\Account\Admin\AdminGroup::class,
    'mitarbeiter',
    [
        'name' => 'mitarbeiter',
        'permission_level' => 1,
    ]
);

$factory->state(
    App\Models\Account\Admin\AdminGroup::class,
    'editor',
    [
        'name' => 'editor',
        'permission_level' => 0,
    ]
);

$factory->state(
    App\Models\Account\Admin\AdminGroup::class,
    'user',
    [
        'name' => 'user',
        'permission_level' => 0,
    ]
);
