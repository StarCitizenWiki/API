<?php

declare(strict_types=1);

use App\Jobs\Rsi\CommLink\Image\ComputeImageHash;
use App\Jobs\Rsi\CommLink\Image\ComputeSimilarImageIds;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Image\ImageHash;
use App\Services\ImageHash\PdqHasher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

it('downloads and stores a pdq hash for a comm-link image', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('GD extension is required for PDQ hashing.');
    }

    Queue::fake();

    $image = Image::factory()->create();

    $uploadedFile = UploadedFile::fake()->image('remote.jpg', 8, 8);
    $contents = file_get_contents($uploadedFile->getPathname());

    Http::fake([
        '*' => Http::response($contents, 200, ['Content-Type' => 'image/png']),
    ]);

    $job = new ComputeImageHash($image->id);
    $job->handle(app(PdqHasher::class));

    $hash = ImageHash::query()->where('comm_link_image_id', $image->id)->first();

    expect($hash)->not->toBeNull()
        ->and($hash->pdq_hash)->toBeString()
        ->and(strlen($hash->pdq_hash))->toBe(256)
        ->and($hash->pdq_quality)->not->toBeNull();

    Queue::assertPushed(ComputeSimilarImageIds::class, fn (ComputeSimilarImageIds $job) => $job->imageId === $image->id);
});
