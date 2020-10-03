<?php declare(strict_types = 1);

use App\Models\Api\StarCitizen\Vehicle\Type\Type;
use App\Models\Api\StarCitizen\Vehicle\Type\TypeTranslation;
use Faker\Generator as Faker;

$factory->define(
    Type::class,
    function (Faker $faker) {
        return [
            'slug' => $faker->unique()->slug,
        ];
    }
);

$factory->define(
    TypeTranslation::class,
    function (Faker $faker) {
        return [
            'locale_code' => 'en_EN',
            'translation' => 'Lorem Ipsum',
        ];
    }
);
