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
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $response = $this->getJson('/api/manufacturers');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.name', $manufacturer->name)
        ->assertJsonPath('data.0.code', $manufacturer->code);
});

it('shows a manufacturer by underscored name', function (): void {
    $manufacturer = Manufacturer::factory()->create([
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

it('searches manufacturers by query', function (): void {
    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Anvil Aerospace',
        'code' => 'ANVL',
    ]);

    $response = $this->postJson('/api/manufacturers/search', [
        'query' => 'Anvil',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.0.name', $manufacturer->name)
        ->assertJsonPath('data.0.code', $manufacturer->code)
        ->assertJsonPath('data.0.uuid', $manufacturer->uuid);
});
