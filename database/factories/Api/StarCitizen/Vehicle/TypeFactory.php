<?php declare(strict_types = 1);

use Faker\Generator as Faker;

$factory->define(
    \App\Models\Api\StarCitizen\Vehicle\Type\Type::class,
    function (Faker $faker) {
        return [];
    }
);

$factory->define(
    \App\Models\Api\StarCitizen\Vehicle\Type\TypeTranslation::class,
    function (Faker $faker) {
        return [
            'locale_code' => 'en_EN',
            'translation' => 'Lorem Ipsum',
        ];
    }
);
