<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Image\ImageHash;
use App\Services\ImageHash\PdqHasher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

it('shows reverse image search results', function (): void {
    $image = Image::factory()->create([
        'src' => '/i/alpha/alpha.webp',
    ]);

    $image->metadata()->create([
        'size' => 4096,
        'mime' => 'image/webp',
        'last_modified' => now(),
    ]);

    $uploadedImage = UploadedFile::fake()->image('search.jpg', 32, 32);
    $contents = file_get_contents($uploadedImage->getPathname());
    expect($contents)->not->toBeFalse();

    $hashResult = app(PdqHasher::class)->hashContents($contents);

    ImageHash::query()->create([
        'comm_link_image_id' => $image->id,
        'pdq_hash' => $hashResult->toBitString(),
        'pdq_quality' => $hashResult->quality,
    ]);

    $response = $this->post(route('web.comm-links.images.index'), [
        'search' => 'reverse-image',
        'image' => $uploadedImage,
        'similarity' => 95,
    ]);

    $response->assertOk()
        ->assertSee('alpha.webp');
});
