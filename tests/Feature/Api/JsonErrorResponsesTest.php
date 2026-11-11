<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use Illuminate\Support\Facades\Route;

it('renders api 404s as json without an accept header', function (): void {
    GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $response = $this->get('/api/vehicles/nonexistent', ['Accept' => '*/*']);

    $response->assertStatus(404);
    $response->assertHeader('Content-Type', 'application/json');
    $response->assertHeader('Cache-Control', 'no-store, private');
    expect($response->json('message'))->toBeString();
});

it('renders api 405s as json without an accept header', function (): void {
    $response = $this->post('/api/vehicles/nonexistent', [], ['Accept' => '*/*']);

    $response->assertStatus(405);
    $response->assertHeader('Content-Type', 'application/json');
    $response->assertHeader('Cache-Control', 'no-store, private');
});

it('renders api validation errors as json without an accept header', function (): void {
    GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $response = $this->get('/api/items?version=not-a-version', ['Accept' => '*/*']);

    $response->assertStatus(422);
    $response->assertHeader('Content-Type', 'application/json');
    $response->assertHeader('Cache-Control', 'no-store, private');
    expect($response->json('errors.version.0'))->toContain('Unknown game version "not-a-version".');
});

it('renders the bare /api path as json with any accept header', function (): void {
    $response = $this->get('/api', ['Accept' => '*/*']);

    $response->assertStatus(404);
    $response->assertHeader('Content-Type', 'application/json');
    $response->assertHeader('Cache-Control', 'no-store, private');
    expect($response->json('message'))->toBeString();
});

it('renders /api with a trailing slash as json with any accept header', function (): void {
    $response = $this->get('/api/', ['Accept' => '*/*']);

    $response->assertStatus(404);
    $response->assertHeader('Content-Type', 'application/json');
    $response->assertHeader('Cache-Control', 'no-store, private');
    expect($response->json('message'))->toBeString();
});

it('keeps html error pages on web routes', function (): void {
    $response = $this->get('/vehicles/nonexistent', ['Accept' => 'text/html']);

    $response->assertStatus(404);
    expect($response->headers->get('Content-Type'))->toStartWith('text/html');
});

it('keeps html error pages on unmatched web paths with any accept header', function (): void {
    $response = $this->get('/definitely-not-a-route', ['Accept' => '*/*']);

    $response->assertStatus(404);
    expect($response->headers->get('Content-Type'))->toStartWith('text/html');
    expect(json_decode($response->getContent(), true))->toBeNull();
});

it('renders api 500s as generic json without an accept header', function (): void {
    config(['app.debug' => false]);

    Route::get('/api/_test_500', static fn () => throw new RuntimeException('boom'));

    $response = $this->get('/api/_test_500', ['Accept' => '*/*']);

    $response->assertStatus(500);
    $response->assertHeader('Content-Type', 'application/json');
    $response->assertHeader('Cache-Control', 'no-store, private');
    expect($response->json('message'))->toBe('Server Error');
    expect($response->getContent())->not->toContain('RuntimeException');
    expect($response->getContent())->not->toContain('boom');
});

it('renders api 500s with exception details when debug is enabled', function (): void {
    config(['app.debug' => true]);

    Route::get('/api/_test_500', static fn () => throw new RuntimeException('boom'));

    $response = $this->get('/api/_test_500', ['Accept' => '*/*']);

    $response->assertStatus(500);
    $response->assertHeader('Content-Type', 'application/json');
    $response->assertHeader('Cache-Control', 'no-store, private');
    expect($response->json('message'))->toBe('boom');
    expect($response->json('exception'))->toBe(RuntimeException::class);
    expect($response->json('trace'))->toBeArray()->not->toBeEmpty();
});
