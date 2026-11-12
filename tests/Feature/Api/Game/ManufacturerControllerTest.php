<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);
});

it('lists manufacturers', function (): void {
    $manufacturer = Manufacturer::factory()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $response = $this->getJson('/api/manufacturers');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.link', route('manufacturers.show', ['manufacturer' => $manufacturer->code]));
})->skip(fn (): bool => DB::connection()->getDriverName() !== 'pgsql', 'PostgreSQL only test')
    ->group('db-pgsql');

it('shows a manufacturer by underscored name', function (): void {
    $manufacturer = Manufacturer::factory()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $response = $this->getJson('/api/manufacturers/Test_Manufacturer');

    $response->assertSuccessful()
        ->assertJsonPath('data.name', $manufacturer->name)
        ->assertJsonPath('data.code', $manufacturer->code)
        ->assertJsonPath('data.uuid', $manufacturer->uuid);
});

it('returns not found for missing manufacturers', function (): void {
    $response = $this->getJson('/api/manufacturers/missing-manufacturer');

    $response->assertNotFound();
});

it('searches manufacturers with plain text queries that are not uuids', function (): void {
    $manufacturer = Manufacturer::factory()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Decari Polo Works',
        'code' => 'DECARI',
    ]);

    $response = $this->postJson('/api/manufacturers/search', [
        'query' => 'decari po',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.0.name', $manufacturer->name)
        ->assertJsonPath('data.0.code', $manufacturer->code)
        ->assertJsonPath('data.0.uuid', $manufacturer->uuid)
        ->assertJsonPath('meta.deprecated', true)
        ->assertHeader('Deprecated', 'true');
})->skip(fn (): bool => DB::connection()->getDriverName() !== 'pgsql', 'PostgreSQL only test')
    ->group('db-pgsql');

it('filters manufacturers by name without ambiguous table error', function (): void {
    Manufacturer::factory()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Roberts Space Industries',
        'code' => 'RSI',
    ]);

    Manufacturer::factory()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Anvil Aerospace',
        'code' => 'ANVL',
    ]);

    $response = $this->getJson('/api/manufacturers?'.http_build_query([
        'filter' => ['name' => 'Roberts Space Industries'],
    ]));

    $response->assertSuccessful();

    $names = collect($response->json('data'))->pluck('name')->all();

    expect($names)->toContain('Roberts Space Industries')
        ->and($names)->not->toContain('Anvil Aerospace');
})->skip(fn (): bool => DB::connection()->getDriverName() !== 'pgsql', 'PostgreSQL only test')
    ->group('db-pgsql');

it('prefers an exact name match over partial matches', function (): void {
    Manufacturer::factory()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Origin Jumpworks',
        'code' => 'ORIJ',
    ]);

    Manufacturer::factory()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Origin Systems',
        'code' => 'ORIS',
    ]);

    $exact = Manufacturer::factory()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Origin',
        'code' => 'ORIG',
    ]);

    $response = $this->getJson('/api/manufacturers/'.rawurlencode('Origin'));

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $exact->uuid)
        ->assertJsonPath('data.name', 'Origin');
})->skip(fn (): bool => DB::connection()->getDriverName() !== 'pgsql', 'PostgreSQL only test')
    ->group('db-pgsql');

it('prefers an exact code match over partial matches', function (): void {
    Manufacturer::factory()->create([
        'uuid' => fake()->uuid(),
        'name' => 'ANVL Industries',
        'code' => 'ANV2',
    ]);

    $exact = Manufacturer::factory()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Anvil Aerospace',
        'code' => 'ANVL',
    ]);

    $response = $this->getJson('/api/manufacturers/ANVL');

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $exact->uuid)
        ->assertJsonPath('data.code', 'ANVL');
})->skip(fn (): bool => DB::connection()->getDriverName() !== 'pgsql', 'PostgreSQL only test')
    ->group('db-pgsql');

it('falls back to the first name-ordered partial match when no exact match exists', function (): void {
    Manufacturer::factory()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Beta Works',
        'code' => 'BETA',
    ]);

    $first = Manufacturer::factory()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Alpha Works',
        'code' => 'ALPH',
    ]);

    $response = $this->getJson('/api/manufacturers/'.rawurlencode('Works'));

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $first->uuid)
        ->assertJsonPath('data.name', 'Alpha Works');
})->skip(fn (): bool => DB::connection()->getDriverName() !== 'pgsql', 'PostgreSQL only test')
    ->group('db-pgsql');

it('treats percent characters as literal text for manufacturer lookups', function (): void {
    Manufacturer::factory()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Test Industries',
        'code' => 'TIND',
    ]);

    $target = Manufacturer::factory()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Test% Works',
        'code' => 'TPWK',
    ]);

    $response = $this->getJson('/api/manufacturers/'.rawurlencode('Test%'));

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $target->uuid)
        ->assertJsonPath('data.name', 'Test% Works');
})->skip(fn (): bool => DB::connection()->getDriverName() !== 'pgsql', 'PostgreSQL only test')
    ->group('db-pgsql');
