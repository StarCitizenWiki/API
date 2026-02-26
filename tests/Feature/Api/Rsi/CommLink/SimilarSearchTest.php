<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

uses(RefreshDatabase::class);

it('returns 401 when not authenticated', function (): void {
    $image = Image::factory()->create();

    $response = $this->getJson("/api/comm-link-images/{$image->id}/similar");

    $response->assertUnauthorized();
});

it('returns 401 when invalid token provided', function (): void {
    $image = Image::factory()->create();

    $response = $this->withToken('invalid-token')
        ->getJson("/api/comm-link-images/{$image->id}/similar");

    $response->assertUnauthorized();
});

it('returns matching similar image contract when authenticated', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $sharedHash = str_repeat('10', 128);

    $queryImage = Image::factory()->create();
    $queryImage->hash()->create([
        'pdq_hash' => $sharedHash,
        'pdq_quality' => 100,
    ]);

    $similarImage = Image::factory()->create();
    $similarImage->hash()->create([
        'pdq_hash' => $sharedHash,
        'pdq_quality' => 100,
    ]);

    $commLink = CommLink::factory()->create([
        'cig_id' => 14001,
        'title' => 'Inside Star Citizen',
    ]);
    $commLink->images()->attach($similarImage->id);

    $response = $this->withToken($token)
        ->getJson("/api/comm-link-images/{$queryImage->id}/similar?similarity=95");

    $response->assertSuccessful()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data', 1)
            ->where('data.0.id', $similarImage->id)
            ->where('data.0.rsi_url', $similarImage->url)
            ->where('data.0.api_url', route('comm-link-images.show', ['image' => $similarImage->id]))
            ->where('data.0.similar_url', route('comm-link-images.similar', ['image' => $similarImage->id]))
            ->has('data.0.comm_links', 1)
            ->where('data.0.comm_links.0.id', $commLink->cig_id)
            ->where('data.0.comm_links.0.title', $commLink->title)
            ->etc()
        );
});

it('rate limits requests to 10 per minute', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $image = Image::factory()->create();

    for ($attempt = 0; $attempt < 10; $attempt++) {
        $this->withToken($token)
            ->getJson("/api/comm-link-images/{$image->id}/similar")
            ->assertSuccessful()
            ->assertJsonStructure(['data']);
    }

    $this->withToken($token)
        ->getJson("/api/comm-link-images/{$image->id}/similar")
        ->assertTooManyRequests()
        ->assertSeeText('Too many similar image searches');
});

it('rate limit resets after minute expires', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $image = Image::factory()->create();

    try {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->withToken($token)
                ->getJson("/api/comm-link-images/{$image->id}/similar")
                ->assertSuccessful();
        }

        $this->withToken($token)
            ->getJson("/api/comm-link-images/{$image->id}/similar")
            ->assertTooManyRequests();

        $this->travel(61)->seconds();

        $this->withToken($token)
            ->getJson("/api/comm-link-images/{$image->id}/similar")
            ->assertSuccessful()
            ->assertJsonStructure(['data']);
    } finally {
        $this->travelBack();
    }
});

it('validates similarity parameter', function (mixed $similarity, bool $shouldSucceed): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $image = Image::factory()->create();

    $response = $this->withToken($token)
        ->getJson("/api/comm-link-images/{$image->id}/similar?similarity={$similarity}");

    if ($shouldSucceed) {
        $response->assertSuccessful()
            ->assertJsonStructure(['data']);

        return;
    }

    $response->assertInvalid(['similarity']);
})->with([
    [0, false],
    [1, true],
    [50, true],
    [100, true],
    [101, false],
    [-10, false],
    ['invalid', false],
]);

it('returns validation error when image id does not exist', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)
        ->getJson('/api/comm-link-images/999999/similar');

    $response->assertInvalid(['image']);
});

it('returns empty collection when image has no hash', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $image = Image::factory()->create();

    $response = $this->withToken($token)
        ->getJson("/api/comm-link-images/{$image->id}/similar");

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});
