<?php

declare(strict_types=1);

use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists manufacturers', function () {
    $manufacturer = Manufacturer::create([
        'uuid' => 'manufacturer-uuid-1',
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $response = $this->getJson('/api/manufacturers');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.name', $manufacturer->name)
        ->assertJsonPath('data.0.code', $manufacturer->code);
});

it('shows a manufacturer by underscored name', function () {
    $manufacturer = Manufacturer::create([
        'uuid' => 'manufacturer-uuid-2',
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $response = $this->getJson('/api/manufacturers/Test_Manufacturer');

    $response->assertSuccessful()
        ->assertJsonPath('data.name', $manufacturer->name)
        ->assertJsonPath('data.code', $manufacturer->code)
        ->assertJsonPath('data.uuid', $manufacturer->uuid);
});

it('returns not found for missing manufacturers', function () {
    $response = $this->getJson('/api/manufacturers/missing-manufacturer');

    $response->assertNotFound();
});

it('searches manufacturers by query', function () {
    $manufacturer = Manufacturer::create([
        'uuid' => 'manufacturer-uuid-3',
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
