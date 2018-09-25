<?php declare(strict_types = 1);

use Faker\Generator as Faker;

$factory->define(
    \App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus::class,
    function (Faker $faker) {
        return [
            'slug' => $faker->slug(2),
        ];
    }
);

$factory->define(
    \App\Models\Api\StarCitizen\ProductionStatus\ProductionStatusTranslation::class,
    function (Faker $faker) {
        return [
            'locale_code' => 'en_EN',
            'translation' => 'Lorem Ipsum',
        ];
    }
);
