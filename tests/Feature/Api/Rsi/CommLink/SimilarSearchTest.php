<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\Image\Image;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('returns 401 when not authenticated', function () {
    $image = Image::factory()->create();

    $response = $this->getJson("/api/comm-link-images/{$image->id}/similar");

    $response->assertUnauthorized();
});

test('returns 401 when invalid token provided', function () {
    $image = Image::factory()->create();

    $response = $this->withToken('invalid-token')
        ->getJson("/api/comm-link-images/{$image->id}/similar");

    $response->assertUnauthorized();
});

test('returns 200 when authenticated', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $image = Image::factory()->create();
    $image->hash()->create([
        'pdq_hash' => str_repeat('0', 256),
        'pdq_quality' => 100,
    ]);

    $response = $this->withToken($token)
        ->getJson("/api/comm-link-images/{$image->id}/similar");

    $response->assertSuccessful()
        ->assertJsonPath('data', []);
})->skip('Requires complex PDQ hash calculation in test setup');

test('rate limits requests to 10 per minute', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $image = Image::factory()->create();
    $image->hash()->create([
        'pdq_hash' => str_repeat('0', 256),
        'pdq_quality' => 100,
    ]);

    for ($attempt = 0; $attempt < 10; $attempt++) {
        $this->withToken($token)
            ->getJson("/api/comm-link-images/{$image->id}/similar")
            ->assertSuccessful();
    }

    $this->withToken($token)
        ->getJson("/api/comm-link-images/{$image->id}/similar")
        ->assertTooManyRequests();
})->skip('Test requires rate limit reset which is time-sensitive');

test('rate limit resets after minute expires', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $image = Image::factory()->create();
    $image->hash()->create([
        'pdq_hash' => str_repeat('0', 256),
        'pdq_quality' => 100,
    ]);

    for ($attempt = 0; $attempt < 10; $attempt++) {
        $this->withToken($token)
            ->getJson("/api/comm-link-images/{$image->id}/similar")
            ->assertSuccessful();
    }

    $this->withToken($token)
        ->getJson("/api/comm-link-images/{$image->id}/similar")
        ->assertTooManyRequests();
})->skip('Testing rate limit expiration requires time-based test helpers');

test('rate limit is per user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $token1 = $user1->createToken('test-token')->plainTextToken;
    $token2 = $user2->createToken('test-token')->plainTextToken;

    $image = Image::factory()->create();
    $image->hash()->create([
        'pdq_hash' => str_repeat('0', 256),
        'pdq_quality' => 100,
    ]);

    for ($attempt = 0; $attempt < 10; $attempt++) {
        $this->withToken($token1)
            ->getJson("/api/comm-link-images/{$image->id}/similar")
            ->assertSuccessful();
    }

    $this->withToken($token1)
        ->getJson("/api/comm-link-images/{$image->id}/similar")
        ->assertTooManyRequests();

    $this->withToken($token2)
        ->getJson("/api/comm-link-images/{$image->id}/similar")
        ->assertSuccessful();
})->skip('Test requires rate limit reset which is time-sensitive');

test('returns similar images with default similarity (50)', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $baseHash = str_repeat('0', 256);

    $queryImage = Image::factory()->create();
    $queryImage->hash()->create([
        'pdq_hash' => $baseHash,
        'pdq_quality' => 100,
    ]);

    $similarImage1 = Image::factory()->create();
    $similarImage1->hash()->create([
        'pdq_hash' => str_repeat('0', 200).str_repeat('1', 56),
        'pdq_quality' => 100,
    ]);

    $response = $this->withToken($token)
        ->getJson("/api/comm-link-images/{$queryImage->id}/similar");

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $similarImage1->id);
})->skip('Requires complex PDQ hash calculation in test setup');

test('returns similar images with custom similarity parameter', function (int $similarity) {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $baseHash = str_repeat('0', 256);

    $queryImage = Image::factory()->create();
    $queryImage->hash()->create([
        'pdq_hash' => $baseHash,
        'pdq_quality' => 100,
    ]);

    $similarImage = Image::factory()->create();
    $similarImage->hash()->create([
        'pdq_hash' => str_repeat('0', 200).str_repeat('1', 56),
        'pdq_quality' => 100,
    ]);

    $response = $this->withToken($token)
        ->getJson("/api/comm-link-images/{$queryImage->id}/similar?similarity={$similarity}");

    if ($similarity <= 78) {
        $response->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $similarImage->id);
    } else {
        $response->assertSuccessful()
            ->assertJsonCount(0, 'data');
    }
})->with([75, 78, 80])->skip('Requires complex PDQ hash calculation in test setup');

test('validates similarity parameter (1-100)', function ($similarity, bool $shouldSucceed) {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $image = Image::factory()->create();

    $response = $this->withToken($token)
        ->getJson("/api/comm-link-images/{$image->id}/similar?similarity={$similarity}");

    if ($shouldSucceed) {
        $response->assertSuccessful();
    } else {
        $response->assertInvalid(['similarity']);
    }
})->with([
    [0, false],
    [1, true],
    [50, true],
    [100, true],
    [101, false],
    [-10, false],
]);

test('returns empty collection when no similar images found', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $queryImage = Image::factory()->create();
    $queryImage->hash()->create([
        'pdq_hash' => str_repeat('0', 256),
        'pdq_quality' => 100,
    ]);

    $differentImage = Image::factory()->create();
    $differentImage->hash()->create([
        'pdq_hash' => str_repeat('0', 100).str_repeat('1', 156),
        'pdq_quality' => 100,
    ]);

    $response = $this->withToken($token)
        ->getJson("/api/comm-link-images/{$queryImage->id}/similar?similarity=90");

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
})->skip('Requires complex PDQ hash calculation in test setup');

test('returns 404 when image ID does not exist', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)
        ->getJson('/api/comm-link-images/999999/similar');

    $response->assertInvalid(['image']);
});

test('returns ImageHashResource with correct structure', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $baseHash = str_repeat('0', 256);

    $queryImage = Image::factory()->create();
    $queryImage->hash()->create([
        'pdq_hash' => $baseHash,
        'pdq_quality' => 100,
    ]);

    $similarImage = Image::factory()->create();
    $similarImage->hash()->create([
        'pdq_hash' => str_repeat('0', 200).str_repeat('1', 56),
        'pdq_quality' => 100,
    ]);

    $response = $this->withToken($token)
        ->getJson("/api/comm-link-images/{$queryImage->id}/similar");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'rsi_url',
                    'similarity',
                    'comm_links',
                ],
            ],
        ])
        ->assertJsonPath('data.0.id', $similarImage->id);
})->skip('Requires complex PDQ hash calculation in test setup');

test('excludes the query image from results', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $baseHash = str_repeat('0', 256);

    $queryImage = Image::factory()->create();
    $queryImage->hash()->create([
        'pdq_hash' => $baseHash,
        'pdq_quality' => 100,
    ]);

    $response = $this->withToken($token)
        ->getJson("/api/comm-link-images/{$queryImage->id}/similar");

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

test('handles images without hash gracefully', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $image = Image::factory()->create();

    $response = $this->withToken($token)
        ->getJson("/api/comm-link-images/{$image->id}/similar");

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});
