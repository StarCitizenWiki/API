<?php declare(strict_types = 1);

use Faker\Generator as Faker;

$factory->define(
    \App\Models\Api\StarCitizen\Vehicle\Focus\Focus::class,
    function (Faker $faker) {
        return [
            'slug' => $faker->slug,
        ];
    }
);

$factory->define(
    \App\Models\Api\StarCitizen\Vehicle\Focus\FocusTranslation::class,
    function (Faker $faker) {
        return [
            'locale_code' => 'en_EN',
            'translation' => 'Lorem Ipsum',
        ];
    }
);
