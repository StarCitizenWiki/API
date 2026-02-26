<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
        ->assertJsonPath('data.0.code', $manufacturer->code);
})->skip(
    fn (): bool => config('database.default') !== 'pgsql',
    'PostgreSQL only test'
)->group('db-pgsql');

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
