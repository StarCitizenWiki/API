<?php declare(strict_types = 1);

use Faker\Generator as Faker;

$factory->define(
    \App\Models\Api\StarCitizen\Manufacturer\Manufacturer::class,
    function (Faker $faker) {
        static $cigId = 1;

        return [
            'cig_id' => $cigId++,
            'name' => $faker->userName,
            'name_short' => strtoupper(str_random($faker->numberBetween(1, 10))),
        ];
    }
);

$factory->define(
    \App\Models\Api\StarCitizen\Manufacturer\ManufacturerTranslation::class,
    function (Faker $faker) {
        return [
            'locale_code' => 'en_EN',
            'known_for' => 'Lorem Ipsum',
            'description' => 'Lorem Ipsum dolor sit amet',
        ];
    }
);

$factory->state(
    \App\Models\Api\StarCitizen\Manufacturer\ManufacturerTranslation::class,
    'german',
    function (Faker $faker) {
        return [
            'locale_code' => 'de_DE',
            'known_for' => 'Deutsches Lorem Ipsum',
            'description' => 'Deutsches Lorem Ipsum',
        ];
    }
);
