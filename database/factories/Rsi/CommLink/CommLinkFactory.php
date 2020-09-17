<?php declare(strict_types = 1);

use Faker\Generator as Faker;

$factory->define(
    \App\Models\Rsi\CommLink\CommLink::class,
    function (Faker $faker) {
        static $cigId = 12663;

        $channel = factory(\App\Models\Rsi\CommLink\Channel\Channel::class)->create();
        $category = factory(\App\Models\Rsi\CommLink\Category\Category::class)->create();
        $series = factory(\App\Models\Rsi\CommLink\Series\Series::class)->create();

        return [
            'cig_id' => $cigId++,

            'title' => $faker->sentence(),
            'comment_count' => $faker->numberBetween(0, 2000),
            'url' => $faker->boolean() ? '/comm-link/SCW/'.$cigId.'-IMPORT' : null,

            'file' => \Carbon\Carbon::now()->format('Y-m-d_His').'.html',

            'channel_id' => $channel->id,
            'category_id' => $category->id,
            'series_id' => $series->id,

            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ];
    }
);

$factory->define(
    \App\Models\Rsi\CommLink\CommLinkTranslation::class,
    function (Faker $faker) {
        return [
            'locale_code' => 'en_EN',
            'translation' => $faker->randomHtml(2, 3),
        ];
    }
);

$factory->state(
    \App\Models\Rsi\CommLink\CommLinkTranslation::class,
    'german',
    function (Faker $faker) {
        return [
            'locale_code' => 'de_DE',
            'translation' => $faker->randomHtml(2, 3),
        ];
    }
);

/**
 * Category, Channel, Series, Images, Links
 */
$factory->define(
    \App\Models\Rsi\CommLink\Channel\Channel::class,
    function (Faker $faker) {
        $slug = $faker->slug;
        $name = ucwords(str_replace('-', ' ', $slug));

        return [
            'name' => $name,
            'slug' => $slug,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ];
    }
);

$factory->define(
    \App\Models\Rsi\CommLink\Category\Category::class,
    function (Faker $faker) {
        $slug = $faker->slug;
        $name = ucwords(str_replace('-', ' ', $slug));

        return [
            'name' => $name,
            'slug' => $slug,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ];
    }
);

$factory->define(
    \App\Models\Rsi\CommLink\Series\Series::class,
    function (Faker $faker) {
        $slug = $faker->slug;
        $name = ucwords(str_replace('-', ' ', $slug));

        return [
            'name' => $name,
            'slug' => $slug,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ];
    }
);

$factory->define(
    \App\Models\Rsi\CommLink\Link\Link::class,
    function (Faker $faker) {
        return [
            'href' => $faker->url,
            'text' => $faker->boolean() ? 'Lorem Ipsum' : null,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ];
    }
);

$factory->define(
    \App\Models\Rsi\CommLink\Image\Image::class,
    function (Faker $faker) {
        $dir = \Illuminate\Support\Str::random(14);
        $fileName = \Illuminate\Support\Str::random(4);

        $file = sprintf('/media/%s/source/%s.jpg', $dir, $fileName);

        return [
            'src' => $file,
            'alt' => $faker->boolean() ? 'Lorem Ipsum' : '',
            'local' => false,
            'dir' => $dir,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ];
    }
);
