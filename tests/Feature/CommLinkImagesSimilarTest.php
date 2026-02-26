<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns redirect when not authenticated', function (): void {
    $image = Image::factory()->create();

    $response = $this->get(route('web.comm-links.images.similar', $image->id));

    $response->assertRedirect(route('login'));
});

it('renders similar search view contract when authenticated', function (): void {
    $user = User::factory()->create();
    $sharedHash = str_repeat('10', 128);

    $queryImage = Image::factory()->create([
        'alt' => 'Query Image',
    ]);
    $queryImage->hash()->create([
        'pdq_hash' => $sharedHash,
        'pdq_quality' => 100,
    ]);

    $similarImage = Image::factory()->create([
        'alt' => 'Similar Image',
    ]);
    $similarImage->hash()->create([
        'pdq_hash' => $sharedHash,
        'pdq_quality' => 100,
    ]);

    $commLink = CommLink::factory()->create([
        'cig_id' => 14001,
        'title' => 'Inside Star Citizen',
    ]);
    $commLink->images()->attach($similarImage->id);

    $response = $this->actingAs($user)
        ->get(route('web.comm-links.images.similar', ['image' => $queryImage->id, 'similarity' => 95]));

    $response->assertOk()
        ->assertViewIs('comm-links.images.index')
        ->assertViewHas('pageTitle', 'Comm-Link Images')
        ->assertViewHas('searchType', 'similar-images')
        ->assertViewHas('searchQuery', sprintf('Similar to image ID %s', $queryImage->id))
        ->assertViewHas('images', function (array $images) use ($similarImage, $commLink): bool {
            return count($images) === 1
                && data_get($images, '0.id') === $similarImage->id
                && data_get($images, '0.rsi_url') === $similarImage->url
                && data_get($images, '0.comm_links.0.id') === $commLink->cig_id
                && data_get($images, '0.comm_links.0.title') === $commLink->title;
        });
});

it('rate limits requests to 10 per minute', function (): void {
    $user = User::factory()->create();
    $image = Image::factory()->create();

    for ($attempt = 0; $attempt < 10; $attempt++) {
        $this->actingAs($user)
            ->get(route('web.comm-links.images.similar', $image->id))
            ->assertOk();
    }

    $this->actingAs($user)
        ->get(route('web.comm-links.images.similar', $image->id))
        ->assertTooManyRequests()
        ->assertSeeText('Too many similar image searches');
});

it('rate limit resets after minute expires', function (): void {
    $user = User::factory()->create();
    $image = Image::factory()->create();

    try {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->actingAs($user)
                ->get(route('web.comm-links.images.similar', $image->id))
                ->assertOk();
        }

        $this->actingAs($user)
            ->get(route('web.comm-links.images.similar', $image->id))
            ->assertTooManyRequests();

        $this->travel(61)->seconds();

        $this->actingAs($user)
            ->get(route('web.comm-links.images.similar', $image->id))
            ->assertOk()
            ->assertViewIs('comm-links.images.index');
    } finally {
        $this->travelBack();
    }
});

it('validates similarity parameter', function (mixed $similarity, bool $shouldSucceed): void {
    $user = User::factory()->create();
    $image = Image::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('web.comm-links.images.similar', ['image' => $image->id, 'similarity' => $similarity]));

    if ($shouldSucceed) {
        $response->assertOk()
            ->assertViewIs('comm-links.images.index');

        return;
    }

    $response->assertSessionHasErrors(['similarity']);
})->with([
    [0, false],
    [1, true],
    [50, true],
    [100, true],
    [101, false],
    [-10, false],
    ['invalid', false],
    [75.5, false],
]);

it('returns 404 when image id does not exist', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('web.comm-links.images.similar', 999999));

    $response->assertNotFound();
});

it('handles images without hash gracefully', function (): void {
    $user = User::factory()->create();
    $image = Image::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('web.comm-links.images.similar', $image->id));

    $response->assertOk()
        ->assertViewIs('comm-links.images.index')
        ->assertViewHas('images', fn (array $images): bool => $images === [])
        ->assertViewHas('searchType', 'similar-images')
        ->assertViewHas('searchQuery', sprintf('Similar to image ID %s', $image->id));
});
