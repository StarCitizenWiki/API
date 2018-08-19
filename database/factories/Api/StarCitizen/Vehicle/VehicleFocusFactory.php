<?php declare(strict_types = 1);

use Faker\Generator as Faker;

$factory->define(
    \App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus::class,
    function (Faker $faker) {
        return [];
    }
);

$factory->define(
    \App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocusTranslation::class,
    function (Faker $faker) {
        return [
            'locale_code' => 'en_EN',
            'translation' => 'Lorem Ipsum',
        ];
    }
);