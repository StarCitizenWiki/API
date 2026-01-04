<?php

declare(strict_types=1);

use App\Http\Requests\Rsi\CommLink\ReverseImageSearchRequest;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Image\ImageHash;
use App\Services\ImageHash\PdqHasher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

test('reverse image search finds a matching comm-link image', function () {
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
        ->assertJsonPath('data.0.rsi_url', $image->url)
        ->assertJsonPath('data.0.similarity', 100);
});

test('reverse image search defaults similarity to 75', function () {
    $request = ReverseImageSearchRequest::create('/api/comm-links/reverse-image-search', 'POST');

    expect($request->similarity())->toBe(75);
});

test('reverse image search rejects non-image uploads', function () {
    $uploadedFile = UploadedFile::fake()->create('not-an-image.txt', 10, 'text/plain');

    $response = $this->postJson('/api/comm-links/reverse-image-search', [
        'image' => $uploadedFile,
    ]);

    $response->assertInvalid(['image']);
});
