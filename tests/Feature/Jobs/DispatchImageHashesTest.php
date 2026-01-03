<?php

declare(strict_types=1);

use App\Jobs\Rsi\CommLink\Image\ComputeImageHash;
use App\Jobs\Rsi\CommLink\Image\DispatchImageHashes;
use App\Models\Rsi\CommLink\Category;
use App\Models\Rsi\CommLink\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

it('dispatches hashing jobs for comm-link images', function () {
    Bus::fake();

    $channel = Channel::query()->create(['name' => 'Transmission', 'slug' => 'transmission']);
    $category = Category::query()->create(['name' => 'General', 'slug' => 'general']);
    $series = Series::query()->create(['name' => 'Series', 'slug' => 'series']);

    $commLink = CommLink::query()->create([
        'cig_id' => 12663,
        'title' => 'Test',
        'comment_count' => 0,
        'url' => null,
        'file' => '2020-01-01_000000.html',
        'channel_id' => $channel->id,
        'category_id' => $category->id,
        'series_id' => $series->id,
        'created_at' => now(),
    ]);

    $image = Image::factory()->create();
    $commLink->images()->attach($image->id);

    (new DispatchImageHashes([$commLink->cig_id]))->handle();

    Bus::assertDispatched(ComputeImageHash::class, function (ComputeImageHash $job) use ($image): bool {
        return $job->imageId === $image->id;
    });
});
