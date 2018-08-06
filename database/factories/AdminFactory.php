<?php declare(strict_types = 1);

use Faker\Generator as Faker;

$factory->define(
    App\Models\Account\Admin\Admin::class,
    function (Faker $faker) {
        return [
            'username' => $faker->userName,
            'blocked' => false,
            'provider' => 'starcitizenwiki',
            'provider_id' => $faker->numberBetween(1, 100),
            'last_login' => $faker->dateTime,
            'created_at' => Carbon\Carbon::now(),
            'updated_at' => Carbon\Carbon::now(),
        ];
    }
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
    'user',
    [
        'name' => 'user',
        'permission_level' => 0,
    ]
);
