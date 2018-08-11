<?php declare(strict_types = 1);

use Faker\Generator as Faker;

$factory->define(
    \App\Models\Api\Notification::class,
    function (Faker $faker) {
        return [
            'level' => $faker->numberBetween(0, 3),
            'content' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.',
            'order' => $faker->numberBetween(0, 4),
            'output_status' => true,
            'output_email' => false,
            'output_index' => false,
            'expired_at' => $faker->dateTime,
            'published_at' => \Carbon\Carbon::now(),
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ];
    }
);

$factory->state(
    \App\Models\Api\Notification::class,
    'active',
    [
        'expired_at' => \Carbon\Carbon::now()->addWeek(),
    ]
);

$factory->state(
    \App\Models\Api\Notification::class,
    'expired',
    [
        'expired_at' => \Carbon\Carbon::now()->subDay(),
    ]
);

$factory->state(
    \App\Models\Api\Notification::class,
    'not_published',
    [
        'published_at' => \Carbon\Carbon::now()->addWeek(),
    ]
);

$factory->state(
    \App\Models\Api\Notification::class,
    'email',
    [
        'output_email' => true,
    ]
);
