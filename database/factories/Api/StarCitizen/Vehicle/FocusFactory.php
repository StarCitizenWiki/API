<?php declare(strict_types = 1);

use App\Models\Api\StarCitizen\Vehicle\Focus\Focus;
use App\Models\Api\StarCitizen\Vehicle\Focus\FocusTranslation;
use Faker\Generator as Faker;

$factory->define(
    Focus::class,
    function (Faker $faker) {
        return [
            'slug' => $faker->unique()->slug,
        ];
    }
);

$factory->define(
    FocusTranslation::class,
    function (Faker $faker) {
        return [
            'locale_code' => 'en_EN',
            'translation' => 'Lorem Ipsum',
        ];
    }
);
