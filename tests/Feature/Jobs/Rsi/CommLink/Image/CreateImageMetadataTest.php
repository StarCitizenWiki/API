<?php

declare(strict_types=1);

use App\Jobs\Rsi\CommLink\Image\CreateImageMetadata;
use App\Jobs\Rsi\CommLink\Image\CreateImageMetadatum;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Image\ImageMetadata;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    $this->commLink = CommLink::factory()->create(['cig_id' => 12663]);
    $this->otherCommLink = CommLink::factory()->create(['cig_id' => 12664]);
});

it('dispatches metadata jobs for attached images without metadata', function (): void {
    Queue::fake();

    $attached = Image::factory()->count(2)->create();
    $this->commLink->images()->attach($attached);

    (new CreateImageMetadata([12663]))->handle();

    Queue::assertPushed(CreateImageMetadatum::class, 2);
    $attached->each(fn (Image $image) => Queue::assertPushed(
        CreateImageMetadatum::class,
        fn (CreateImageMetadatum $job): bool => $job->imageId === $image->id
    ));
});

it('re-dispatches images with an undefined metadata placeholder', function (): void {
    Queue::fake();

    $undefined = Image::factory()->create();
    $undefined->metadata()->create([
        'mime' => 'undefined',
        'size' => 0,
        'last_modified' => '0001-01-01 00:00:00',
    ]);
    $this->commLink->images()->attach($undefined);

    (new CreateImageMetadata([12663]))->handle();

    Queue::assertPushed(CreateImageMetadatum::class, fn (CreateImageMetadatum $job): bool => $job->imageId === $undefined->id);
});

it('skips images with real metadata', function (): void {
    Queue::fake();

    $fetched = Image::factory()->create();
    $fetched->metadata()->create([
        'mime' => 'image/webp',
        'size' => 1024,
        'last_modified' => now(),
    ]);
    $this->commLink->images()->attach($fetched);

    (new CreateImageMetadata([12663]))->handle();

    Queue::assertNotPushed(CreateImageMetadatum::class);
});

it('restricts dispatching to the given comm links', function (): void {
    Queue::fake();

    $own = Image::factory()->create();
    $this->commLink->images()->attach($own);

    $foreign = Image::factory()->create();
    $this->otherCommLink->images()->attach($foreign);

    (new CreateImageMetadata([12663]))->handle();

    Queue::assertPushed(CreateImageMetadatum::class, 1);
    Queue::assertPushed(CreateImageMetadatum::class, fn (CreateImageMetadatum $job): bool => $job->imageId === $own->id);
});

it('skips unattached images when no comm link ids are given', function (): void {
    Queue::fake();

    $attached = Image::factory()->create();
    $this->commLink->images()->attach($attached);
    Image::factory()->create();

    (new CreateImageMetadata)->handle();

    Queue::assertPushed(CreateImageMetadatum::class, 1);
    Queue::assertPushed(CreateImageMetadatum::class, fn (CreateImageMetadatum $job): bool => $job->imageId === $attached->id);
});

it('keeps image metadata rows untouched', function (): void {
    $undefined = Image::factory()->create();
    $undefined->metadata()->create([
        'mime' => 'undefined',
        'size' => 0,
        'last_modified' => '0001-01-01 00:00:00',
    ]);
    $this->commLink->images()->attach($undefined);

    Queue::fake();
    (new CreateImageMetadata([12663]))->handle();

    expect(ImageMetadata::query()->where('comm_link_image_id', $undefined->id)->first()->mime)->toBe('undefined');
});
