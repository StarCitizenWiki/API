<?php

declare(strict_types=1);

use App\Jobs\Rsi\CommLink\Image\CreateImageMetadatum;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Support\Facades\Queue;

it('dispatches metadata jobs for images with missing or undefined metadata', function (): void {
    Queue::fake();

    $commLink = CommLink::factory()->create(['cig_id' => 12663]);

    $missing = Image::factory()->create();
    $undefined = Image::factory()->create();
    $undefined->metadata()->create([
        'mime' => 'undefined',
        'size' => 0,
        'last_modified' => '0001-01-01 00:00:00',
    ]);
    $fetched = Image::factory()->create();
    $fetched->metadata()->create([
        'mime' => 'image/webp',
        'size' => 1024,
        'last_modified' => now(),
    ]);
    $unattached = Image::factory()->create();

    $commLink->images()->attach([$missing->id, $undefined->id, $fetched->id]);

    $this->artisan('comm-link:backfill-image-metadata')
        ->expectsOutputToContain('Dispatched 2 metadata jobs to queue "default".')
        ->assertSuccessful();

    Queue::assertPushed(CreateImageMetadatum::class, 2);
    Queue::assertPushed(CreateImageMetadatum::class, fn (CreateImageMetadatum $job): bool => $job->imageId === $missing->id);
    Queue::assertPushed(CreateImageMetadatum::class, fn (CreateImageMetadatum $job): bool => $job->imageId === $undefined->id);
});

it('dispatches metadata jobs to the configured queue', function (): void {
    Queue::fake();

    $commLink = CommLink::factory()->create(['cig_id' => 12663]);
    $image = Image::factory()->create();
    $commLink->images()->attach($image);

    $this->artisan('comm-link:backfill-image-metadata', ['--queue' => 'expensive'])
        ->expectsOutputToContain('queue "expensive".')
        ->assertSuccessful();

    Queue::assertPushedOn('expensive', CreateImageMetadatum::class);
});
