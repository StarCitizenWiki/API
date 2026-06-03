<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Image\ImageHash;
use App\Services\ImageHash\PdqHasher;
use Illuminate\Http\UploadedFile;

it('reverse image search finds a matching comm-link image', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('GD extension is required for PDQ hashing.');
    }

    $uploadedFile = UploadedFile::fake()->image('match.jpg', 8, 8);
    $contents = file_get_contents($uploadedFile->getPathname());

    $hasher = app(PdqHasher::class);
    $hashResult = $hasher->hashContents($contents);

    $image = Image::factory()->create();
    $image->hash()->create([
        'pdq_hash' => $hashResult->toBitString(),
        'pdq_quality' => $hashResult->quality,
    ]);

    expect(ImageHash::similarImagesForHash($hashResult->toBitString(), 90))->not->toBeEmpty();

    $response = $this->postJson('/api/comm-links/reverse-image-search?similarity=90', [
        'image' => $uploadedFile,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.0.rsi_url', $image->url);
});

it('reverse image search defaults similarity to 75', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('GD extension is required for PDQ hashing.');
    }

    $uploadedFile = UploadedFile::fake()->image('default-similarity.jpg', 8, 8);
    $contents = file_get_contents($uploadedFile->getPathname());

    $hasher = app(PdqHasher::class);
    $hashResult = $hasher->hashContents($contents);

    $image = Image::factory()->create();
    $image->hash()->create([
        'pdq_hash' => $hashResult->toBitString(),
        'pdq_quality' => $hashResult->quality,
    ]);

    $response = $this->postJson('/api/comm-links/reverse-image-search', [
        'image' => $uploadedFile,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.0.rsi_url', $image->url);
});

it('eager loads metadata by default for image resources', function (): void {
    $image = Image::factory()->create();
    $image->metadata()->create([
        'size' => 1024,
        'mime' => 'image/webp',
        'last_modified' => now(),
    ]);

    $loadedImage = Image::query()->findOrFail($image->id);

    expect($loadedImage->relationLoaded('metadata'))->toBeTrue();
});

it('reverse image search rejects non-image uploads', function () {
    $uploadedFile = UploadedFile::fake()->create('not-an-image.txt', 10, 'text/plain');

    $response = $this->postJson('/api/comm-links/reverse-image-search', [
        'image' => $uploadedFile,
    ]);

    $response->assertInvalid(['image']);
});
