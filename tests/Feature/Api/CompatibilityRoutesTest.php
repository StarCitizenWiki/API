<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\System\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('serves the OpenAPI YAML on GET /api/openapi', function (): void {
    $response = $this->get('/api/openapi');

    $response->assertSuccessful()
        ->assertHeader('Content-Type', 'application/yaml');

    expect($response->getContent())->toContain('openapi:');
});

it('serves the OpenAPI YAML headers on HEAD /api/openapi', function (): void {
    $response = $this->head('/api/openapi');

    $response->assertSuccessful()
        ->assertHeader('Content-Type', 'application/yaml');
});

it('serves the OpenAPI YAML on GET /api/v2/openapi', function (): void {
    $response = $this->get('/api/v2/openapi');

    $response->assertSuccessful()
        ->assertHeader('Content-Type', 'application/yaml');

    expect($response->getContent())->toContain('openapi:');
});

it('serves the OpenAPI YAML headers on HEAD /api/v2/openapi', function (): void {
    $response = $this->head('/api/v2/openapi');

    $response->assertSuccessful()
        ->assertHeader('Content-Type', 'application/yaml');
});

it('returns unauthorized for guests on GET /api/user', function (): void {
    $response = $this->getJson('/api/user');

    $response->assertUnauthorized();
});

it('returns the authenticated user contract on GET /api/user', function (): void {
    $language = Language::factory()->create([
        'code' => Language::ENGLISH,
    ]);

    $user = User::factory()->create([
        'name' => 'Compatibility Tester',
        'email' => 'compatibility.tester@example.test',
        'is_admin' => true,
        'language_id' => $language->id,
        'password' => 'secret-password',
        'remember_token' => 'sensitive-token',
    ]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/user');

    $response->assertSuccessful()
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('name', 'Compatibility Tester')
        ->assertJsonPath('email', 'compatibility.tester@example.test')
        ->assertJsonPath('is_admin', true)
        ->assertJsonPath('language_id', $language->id)
        ->assertJsonMissingPath('password')
        ->assertJsonMissingPath('remember_token');
});

it('redirects GET /api/v2/{any?} to /api with 308 and preserves query string', function (): void {
    GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $response = $this->get('/api/v2/legacy/endpoint?baz=qux&foo=bar');

    $response->assertPermanentRedirect()
        ->assertLocation(url('/api/legacy/endpoint').'?baz=qux&foo=bar');
});

it('redirects POST /api/v2/{any?} to /api with 308 and preserves query string', function (): void {
    GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $response = $this->post('/api/v2/legacy/search?baz=qux&foo=bar', [
        'term' => 'vehicle',
    ]);

    $response->assertPermanentRedirect()
        ->assertLocation(url('/api/legacy/search').'?baz=qux&foo=bar');
});
