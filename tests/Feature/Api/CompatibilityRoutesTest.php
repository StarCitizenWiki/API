<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\System\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('serves the openapi yaml on get /api/openapi', function (): void {
    $response = $this->get('/api/openapi');

    $response->assertSuccessful()
        ->assertHeader('Content-Type', 'application/yaml');

    expect($response->getContent())->toContain('openapi:');
});

it('serves the openapi yaml headers on head /api/openapi', function (): void {
    $response = $this->head('/api/openapi');

    $response->assertSuccessful()
        ->assertHeader('Content-Type', 'application/yaml');
});

it('serves the openapi yaml on get /api/v2/openapi', function (): void {
    $response = $this->get('/api/v2/openapi');

    $response->assertSuccessful()
        ->assertHeader('Content-Type', 'application/yaml');

    expect($response->getContent())->toContain('openapi:');
});

it('serves the openapi yaml headers on head /api/v2/openapi', function (): void {
    $response = $this->head('/api/v2/openapi');

    $response->assertSuccessful()
        ->assertHeader('Content-Type', 'application/yaml');
});

it('returns unauthorized for guests on get /api/user', function (): void {
    $response = $this->getJson('/api/user');

    $response->assertUnauthorized();
});

it('returns the authenticated user contract on get /api/user', function (): void {
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

it('redirects get /api/v2/{any?} to /api with 308 and preserves query string', function (): void {
    GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $response = $this->get('/api/v2/legacy/endpoint?baz=qux&foo=bar');

    assertApiV2Redirect($response, '/api/legacy/endpoint', [
        'baz' => 'qux',
        'foo' => 'bar',
    ]);
});

it('redirects post /api/v2/{any?} to /api with 308 and preserves query string', function (): void {
    GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $response = $this->post('/api/v2/legacy/search?baz=qux&foo=bar', [
        'term' => 'vehicle',
    ]);

    assertApiV2Redirect($response, '/api/legacy/search', [
        'baz' => 'qux',
        'foo' => 'bar',
    ]);
});

it('redirects the api v2 root to api without adding a trailing segment', function (): void {
    GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $response = $this->get('/api/v2');

    assertApiV2Redirect($response, '/api', []);
});

/**
 * @param  array<string, string>  $expectedQuery
 */
function assertApiV2Redirect(TestResponse $response, string $expectedPath, array $expectedQuery): void
{
    $response->assertStatus(308);

    $location = (string) $response->headers->get('Location');

    expect(parse_url($location, PHP_URL_PATH))->toBe($expectedPath);

    $actualQuery = [];
    parse_str((string) parse_url($location, PHP_URL_QUERY), $actualQuery);

    expect($actualQuery)->toBe($expectedQuery);
}
