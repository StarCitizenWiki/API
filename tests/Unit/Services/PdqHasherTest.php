<?php

declare(strict_types=1);

use App\Services\ImageHash\PdqHasher;
use Illuminate\Http\UploadedFile;

it('hashes image contents into a 256-bit pdq hash', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('GD extension is required for PDQ hashing.');
    }

    $uploadedFile = UploadedFile::fake()->image('hash.jpg', 8, 8);
    $contents = file_get_contents($uploadedFile->getPathname());

    $hasher = app(PdqHasher::class);
    $result = $hasher->hashContents($contents);

    expect($result->toBitString())->toHaveLength(256)
        ->and($result->quality)->toBeInt()
        ->and($result->quality)->toBeGreaterThanOrEqual(0)
        ->and($result->quality)->toBeLessThanOrEqual(100);
});

it('produces deterministic hashes for identical input', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('GD extension is required for PDQ hashing.');
    }

    $uploadedFile = UploadedFile::fake()->image('hash.jpg', 8, 8);
    $contents = file_get_contents($uploadedFile->getPathname());

    $hasher = app(PdqHasher::class);

    expect($hasher->hashContents($contents)->toBitString())
        ->toBe($hasher->hashContents($contents)->toBitString());
});