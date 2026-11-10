<?php

declare(strict_types=1);

use App\Jobs\Rsi\CommLink\Image\CreateImageMetadatum;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Image\ImageMetadata;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->image = Image::factory()->create();
});

it('stores image metadata from a successful head request', function (): void {
    Http::fake([
        $this->image->url => Http::response('', 200, [
            'Content-Type' => 'image/webp',
            'Content-Length' => '4096',
            'Last-Modified' => 'Wed, 21 Oct 2026 19:00:00 GMT',
        ]),
    ]);

    (new CreateImageMetadatum($this->image->id))->handle();

    $metadata = ImageMetadata::query()->where('comm_link_image_id', $this->image->id)->firstOrFail();

    expect($metadata->mime)->toBe('image/webp')
        ->and($metadata->size)->toBe(4096)
        ->and($metadata->last_modified->format('Y-m-d H:i:s'))->toBe('2026-10-21 19:00:00');
});

it('replaces an undefined placeholder with fetched metadata', function (): void {
    $this->image->metadata()->create([
        'mime' => 'undefined',
        'size' => 0,
        'last_modified' => '0001-01-01 00:00:00',
    ]);

    Http::fake([
        $this->image->url => Http::response('', 200, [
            'Content-Type' => 'image/png',
            'Content-Length' => '73278',
            'Last-Modified' => 'Wed, 21 Oct 2026 19:00:00 GMT',
        ]),
    ]);

    (new CreateImageMetadatum($this->image->id))->handle();

    $metadata = ImageMetadata::query()->where('comm_link_image_id', $this->image->id)->firstOrFail();

    expect($metadata->mime)->toBe('image/png')
        ->and($metadata->size)->toBe(73278);
});

it('writes the undefined placeholder on client error', function (): void {
    Http::fake([
        $this->image->url => Http::response('', 404),
    ]);

    (new CreateImageMetadatum($this->image->id))->handle();

    $metadata = ImageMetadata::query()->where('comm_link_image_id', $this->image->id)->firstOrFail();

    expect($metadata->mime)->toBe('undefined')
        ->and($metadata->size)->toBe(0);
});

it('keeps real metadata on client error', function (): void {
    $this->image->metadata()->create([
        'mime' => 'image/webp',
        'size' => 1024,
        'last_modified' => now(),
    ]);

    Http::fake([
        $this->image->url => Http::response('', 403),
    ]);

    (new CreateImageMetadatum($this->image->id))->handle();

    $metadata = ImageMetadata::query()->where('comm_link_image_id', $this->image->id)->firstOrFail();

    expect($metadata->mime)->toBe('image/webp')
        ->and($metadata->size)->toBe(1024);
});
