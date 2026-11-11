<?php

declare(strict_types=1);

use App\Http\Controllers\Api\UserController;
use App\Models\Game\GameVersion;
use App\Models\System\Language;
use App\Models\User;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;

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

it('serves the openapi yaml on get /api/v3/openapi', function (): void {
    $response = $this->get('/api/v3/openapi');

    $response->assertSuccessful()
        ->assertHeader('Content-Type', 'application/yaml');

    expect($response->getContent())->toContain('openapi:');
});

it('serves the openapi yaml headers on head /api/v3/openapi', function (): void {
    $response = $this->head('/api/v3/openapi');

    $response->assertSuccessful()
        ->assertHeader('Content-Type', 'application/yaml');
});

it('serves the openapi yaml on get /api/v3/openapi with an unknown version parameter', function (): void {
    $response = $this->get('/api/v3/openapi?version=not-a-version');

    $response->assertSuccessful()
        ->assertHeader('Content-Type', 'application/yaml');
});

it('routes get /api/user through the documented controller', function (): void {
    $route = Route::getRoutes()->match(HttpRequest::create('/api/user', 'GET'));

    expect($route->getActionName())->toBe(UserController::class);
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
        'two_factor_secret' => 'secret-ciphertext',
        'two_factor_recovery_codes' => json_encode(['code1', 'code2']),
        'two_factor_confirmed_at' => now(),
    ]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/user');

    $response->assertSuccessful()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('name', 'Compatibility Tester')
        ->assertJsonPath('email', 'compatibility.tester@example.test')
        ->assertJsonPath('is_admin', true)
        ->assertJsonPath('language_id', $language->id)
        ->assertJsonMissingPath('password')
        ->assertJsonMissingPath('remember_token')
        ->assertJsonMissingPath('two_factor_secret')
        ->assertJsonMissingPath('two_factor_recovery_codes')
        ->assertJsonMissingPath('two_factor_confirmed_at');
});

it('rejects expired sanctum tokens on get /api/user', function (): void {
    config(['sanctum.expiration' => 1]);

    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $this->travel(2)->minutes();

    $this->getJson('/api/user', ['Authorization' => "Bearer {$token}"])
        ->assertUnauthorized();
});

it('redirects get /api/v2/{any?} to /api with 308 and preserves query string', function (): void {
    GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $response = $this->get('/api/v2/legacy/endpoint?baz=qux&foo=bar');

    assertApiRedirect($response, '/api/legacy/endpoint', [
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

    assertApiRedirect($response, '/api/legacy/search', [
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

    assertApiRedirect($response, '/api', []);
});

it('redirects get /api/v2/{any?} preserving the raw query string', function (): void {
    GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $response = $this->get('/api/v2/items?page[size]=50&page=2');

    $response->assertStatus(308);

    expect((string) $response->headers->get('Location'))
        ->toEndWith('/api/items?page[size]=50&page=2');
});

it('rejects traversal segments in get /api/v2/{any?} with a json 404', function (): void {
    GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $response = $this->get('/api/v2/..%2Fprofile', ['Accept' => '*/*']);

    $response->assertStatus(404)
        ->assertHeader('Content-Type', 'application/json')
        ->assertHeaderMissing('Location');

    expect($response->json('message'))->toBeString();
});

it('rejects encoded delimiters in get /api/v2/{any?} with a json 404', function (): void {
    GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $response = $this->get('/api/v2/items%3Ffoo', ['Accept' => '*/*']);

    $response->assertStatus(404)
        ->assertHeader('Content-Type', 'application/json')
        ->assertHeaderMissing('Location');

    expect($response->json('message'))->toBeString();
});

it('returns a json 404 without a redirect for unknown api v3 paths', function (): void {
    $response = $this->get('/api/v3/items', ['Accept' => '*/*']);

    $response->assertStatus(404)
        ->assertHeader('Content-Type', 'application/json')
        ->assertHeaderMissing('Location');

    expect($response->json('message'))->toBeString();
});

it('keeps api v3 vehicle routes on the controller', function (): void {
    GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $this->getJson('/api/v3/vehicles?limit=1')
        ->assertOk()
        ->assertHeaderMissing('Location');
});

/**
 * @param  array<string, string>  $expectedQuery
 */
function assertApiRedirect(TestResponse $response, string $expectedPath, array $expectedQuery): void
{
    $response->assertStatus(308);

    $location = (string) $response->headers->get('Location');

    expect(parse_url($location, PHP_URL_PATH))->toBe($expectedPath);

    $actualQuery = [];
    parse_str((string) parse_url($location, PHP_URL_QUERY), $actualQuery);

    expect($actualQuery)->toBe($expectedQuery);
}
