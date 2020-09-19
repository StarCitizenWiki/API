<?php declare(strict_types = 1);

use App\Models\Api\StarCitizen\Vehicle\Size\Size;
use App\Models\Api\StarCitizen\Vehicle\Size\SizeTranslation;
use Faker\Generator as Faker;

$factory->define(
    Size::class,
    function (Faker $faker) {
        return [
            'slug' => $faker->unique()->slug,
        ];
    }
);

$factory->define(
    SizeTranslation::class,
    function (Faker $faker) {
        return [
            'locale_code' => 'en_EN',
            'translation' => 'Lorem Ipsum',
        ];
    }
);
