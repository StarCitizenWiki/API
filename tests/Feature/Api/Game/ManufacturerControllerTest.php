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
        ->assertJsonPath('data.0.name', $manufacturer->name)
        ->assertJsonPath('data.0.code', $manufacturer->code)
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
