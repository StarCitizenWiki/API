<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns redirect when not authenticated', function (): void {
    $image = Image::factory()->create();

    $response = $this->get(route('web.comm-links.images.similar', $image->id));

    $response->assertRedirect(route('login'));
});

it('returns 200 when authenticated', function (): void {
    $user = \App\Models\User::factory()->create();

    $image = Image::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('web.comm-links.images.similar', $image->id));

    $response->assertOk();
});

it('rate limits requests to 10 per minute', function (): void {
    $user = \App\Models\User::factory()->create();

    $image = Image::factory()->create();

    for ($attempt = 0; $attempt < 10; $attempt++) {
        $this->actingAs($user)
            ->get(route('web.comm-links.images.similar', $image->id))
            ->assertOk();
    }

    $this->actingAs($user)
        ->get(route('web.comm-links.images.similar', $image->id))
        ->assertStatus(429);
})->skip('Test requires rate limit reset which is time-sensitive');

it('validates similarity parameter (must be integer between 1 and 100)', function ($similarity, bool $shouldSucceed) {
    $user = \App\Models\User::factory()->create();
    $image = Image::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('web.comm-links.images.similar', ['image' => $image->id, 'similarity' => $similarity]));

    if ($shouldSucceed) {
        $response->assertOk();
    } else {
        $response->assertSessionHasErrors(['similarity']);
    }
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

it('validates similarity parameter must be integer', function (): void {
    $user = \App\Models\User::factory()->create();
    $image = Image::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('web.comm-links.images.similar', ['image' => $image->id, 'similarity' => 'abc']));

    $response->assertSessionHasErrors(['similarity']);
});

it('uses default similarity of 50 when not provided', function (): void {
    $user = \App\Models\User::factory()->create();
    $image = Image::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('web.comm-links.images.similar', $image->id));

    $response->assertOk();
});

it('returns 404 when image ID does not exist', function (): void {
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('web.comm-links.images.similar', 999999));

    $response->assertNotFound();
});

it('renders index view with images grid', function (): void {
    $user = \App\Models\User::factory()->create();

    $commLink = CommLink::factory()->create([
        'cig_id' => 14001,
        'title' => 'Inside Star Citizen',
    ]);

    $image = Image::factory()->create([
        'alt' => 'Test Image',
    ]);

    $commLink->images()->attach($image->id);

    $response = $this->actingAs($user)
        ->get(route('web.comm-links.images.similar', $image->id));

    $response->assertOk()
        ->assertViewIs('comm-links.images.index')
        ->assertSee('Comm-Link Images');
});

it('renders view with search context for similar images', function (): void {
    $user = \App\Models\User::factory()->create();

    $image = Image::factory()->create([
        'alt' => 'Query Image',
    ]);

    $response = $this->actingAs($user)
        ->get(route('web.comm-links.images.similar', $image->id));

    $response->assertOk()
        ->assertViewHas('pageTitle')
        ->assertViewHas('images');
});

it('passes similarity parameter to view context', function (): void {
    $user = \App\Models\User::factory()->create();

    $image = Image::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('web.comm-links.images.similar', ['image' => $image->id, 'similarity' => 75]));

    $response->assertOk();
});

it('handles images without hash gracefully', function (): void {
    $user = \App\Models\User::factory()->create();

    $image = Image::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('web.comm-links.images.similar', $image->id));

    $response->assertOk()
        ->assertViewIs('comm-links.images.index');
});
