<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filters comm-link images by media name and excludes derived or empty matches', function (): void {
    $matchingImage = Image::factory()->create([
        'src' => '/i/carrack/carrack.webp',
    ]);

    $matchingImage->metadata()->create([
        'size' => 2048,
        'mime' => 'image/webp',
        'last_modified' => now(),
    ]);

    $derivedImage = Image::factory()->create([
        'src' => '/i/carrack/carrack-duplicate.webp',
        'base_image_id' => $matchingImage->id,
    ]);

    $derivedImage->metadata()->create([
        'size' => 2048,
        'mime' => 'image/webp',
        'last_modified' => now(),
    ]);

    $zeroSizedImage = Image::factory()->create([
        'src' => '/i/carrack/carrack-empty.webp',
    ]);

    $zeroSizedImage->metadata()->create([
        'size' => 0,
        'mime' => 'image/webp',
        'last_modified' => now(),
    ]);

    $otherImage = Image::factory()->create([
        'src' => '/i/cutlass/cutlass.webp',
    ]);

    $otherImage->metadata()->create([
        'size' => 2048,
        'mime' => 'image/webp',
        'last_modified' => now(),
    ]);

    $response = $this->get(route('web.comm-links.images.search', [
        'query' => 'carrack',
    ]));

    $response->assertOk()
        ->assertViewIs('comm-links.images.index')
        ->assertViewHas('searchType', 'media-name')
        ->assertViewHas('searchQuery', 'carrack')
        ->assertViewHas('images', function (array $images) use ($matchingImage): bool {
            return count($images) === 1
                && data_get($images, '0.id') === $matchingImage->id
                && data_get($images, '0.rsi_url') === $matchingImage->url;
        })
        ->assertSee('data-testid="comm-links-images-heading"', false)
        ->assertSee('data-testid="comm-links-images-search-summary"', false)
        ->assertSee('data-testid="comm-link-image-card-'.$matchingImage->id.'"', false)
        ->assertSee('data-testid="comm-link-image-details-link-'.$matchingImage->id.'"', false)
        ->assertSee('data-testid="comm-link-image-source-link-'.$matchingImage->id.'"', false)
        ->assertSeeText('Showing results for media name search: carrack.')
        ->assertSeeText('carrack.webp')
        ->assertSeeText('Source')
        ->assertSee($matchingImage->url, false)
        ->assertSee(route('web.comm-links.images.show', $matchingImage->id), false)
        ->assertDontSee('data-testid="comm-link-image-card-'.$derivedImage->id.'"', false)
        ->assertDontSee('data-testid="comm-link-image-card-'.$zeroSizedImage->id.'"', false)
        ->assertDontSee('data-testid="comm-link-image-card-'.$otherImage->id.'"', false)
        ->assertDontSeeText('carrack-duplicate.webp')
        ->assertDontSeeText('carrack-empty.webp')
        ->assertDontSeeText('cutlass.webp')
        ->assertDontSeeText('No images available.');
});

it('returns an empty media-name result set for an empty query', function (): void {
    Image::factory()->create([
        'src' => '/i/carrack/carrack.webp',
    ])->metadata()->create([
        'size' => 2048,
        'mime' => 'image/webp',
        'last_modified' => now(),
    ]);

    $response = $this->get(route('web.comm-links.images.search'));

    $response->assertOk()
        ->assertViewIs('comm-links.images.index')
        ->assertViewHas('searchType', 'media-name')
        ->assertViewHas('searchQuery', '')
        ->assertViewHas('images', [])
        ->assertSee('data-testid="comm-links-images-search-summary"', false)
        ->assertSee('data-testid="comm-links-images-empty-state"', false)
        ->assertSeeText('Showing results for media name search: -.')
        ->assertSeeText('No images available.')
        ->assertDontSeeText('carrack.webp');
});
