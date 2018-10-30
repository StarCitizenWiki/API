<?php declare(strict_types = 1);

use Faker\Generator as Faker;

$factory->define(
    \App\Models\Api\StarCitizen\Vehicle\Size\Size::class,
    function (Faker $faker) {
        return [
            'slug' => $faker->unique()->slug,
        ];
    }
);

$factory->define(
    \App\Models\Api\StarCitizen\Vehicle\Size\SizeTranslation::class,
    function (Faker $faker) {
        return [
            'locale_code' => 'en_EN',
            'translation' => 'Lorem Ipsum',
        ];
    }
);
