<?php declare(strict_types = 1);

use App\Models\Rsi\CommLink\Category\Category;
use App\Models\Rsi\CommLink\Channel\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\CommLinkTranslation;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Image\ImageMetadata;
use App\Models\Rsi\CommLink\Link\Link;
use App\Models\Rsi\CommLink\Series\Series;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(
    CommLink::class,
    function (Faker $faker) {
        static $cigId = 12663;

        $channel = factory(Channel::class)->create();
        $category = factory(Category::class)->create();
        $series = factory(Series::class)->create();

        return [
            'cig_id' => $cigId++,

            'title' => $faker->sentence(),
            'comment_count' => $faker->numberBetween(0, 2000),
            'url' => $faker->boolean() ? '/comm-link/SCW/'.$cigId.'-IMPORT' : null,

            'file' => Carbon::now()->format('Y-m-d_His').'.html',

            'channel_id' => $channel->id,
            'category_id' => $category->id,
            'series_id' => $series->id,

            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
);

$factory->define(
    CommLinkTranslation::class,
    function (Faker $faker) {
        return [
            'locale_code' => 'en_EN',
            'translation' => $faker->randomHtml(2, 3),
        ];
    }
);

$factory->state(
    CommLinkTranslation::class,
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
    Channel::class,
    function (Faker $faker) {
        $slug = $faker->slug;
        $name = ucwords(str_replace('-', ' ', $slug));

        return [
            'name' => $name,
            'slug' => $slug,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
);

$factory->define(
    Category::class,
    function (Faker $faker) {
        $slug = $faker->slug;
        $name = ucwords(str_replace('-', ' ', $slug));

        return [
            'name' => $name,
            'slug' => $slug,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
);

$factory->define(
    Series::class,
    function (Faker $faker) {
        $slug = $faker->slug;
        $name = ucwords(str_replace('-', ' ', $slug));

        return [
            'name' => $name,
            'slug' => $slug,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
);

$factory->define(
    Link::class,
    function (Faker $faker) {
        return [
            'href' => $faker->url,
            'text' => $faker->boolean() ? $faker->text($faker->numberBetween(10, 100)) : '',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
);

$factory->define(
    Image::class,
    function (Faker $faker) {
        $dir = Str::random(14);
        $fileName = Str::random(4);

        $file = sprintf('/media/%s/source/%s.jpg', $dir, $fileName);

        return [
            'src' => $file,
            'alt' => $faker->boolean() ? 'Lorem Ipsum' : '',
            'local' => false,
            'dir' => $dir,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
);

$factory->define(
    ImageMetadata::class,
    function (Faker $faker) {
        return [
            'size' => $faker->randomNumber(5),
            'mime' => 'image/jpeg',
            'last_modified' => $faker->dateTime(),
        ];
    }
);
