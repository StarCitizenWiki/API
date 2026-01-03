<?php

declare(strict_types=1);

use App\Services\ImageHash\PdqHasher;
use Illuminate\Http\UploadedFile;

test('it hashes image contents into a 256-bit PDQ hash', function () {
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
