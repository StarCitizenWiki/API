<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->defaultVersion = GameVersion::factory()->create(['is_default' => true]);
});

it('sorts vehicles by name ascending', function () {
    foreach (['Avenger', 'Cutlass', 'Freelancer', 'Hornet', 'Mustang'] as $name) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'name' => $name,
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=name');

    $response->assertSuccessful();
    $names = collect($response->json('data'))->pluck('name')->toArray();
    expect($names)->toBe(['Avenger', 'Cutlass', 'Freelancer', 'Hornet', 'Mustang']);
});

it('sorts vehicles by size descending', function () {
    foreach ([1, 3, 2, 4] as $size) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'size' => $size,
            'name' => "Ship Size {$size}",
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=-size');

    $response->assertSuccessful();
    $sizes = collect($response->json('data'))->pluck('size')->toArray();
    expect($sizes)->toBe([4, 3, 2, 1]);
});

it('sorts vehicles by cargo capacity descending', function () {
    $cargos = [100, 500, 250, 1000];

    foreach ($cargos as $cargo) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'data' => ['Cargo' => $cargo],
            'name' => "Ship Cargo {$cargo}",
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=-cargo_capacity');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('data.Cargo')->toArray();
    expect($returned)->toBe([1000, 500, 250, 100]);
});

it('sorts vehicles by SCM speed ascending', function () {
    $speeds = [150, 220, 180];

    foreach ($speeds as $speed) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'data' => ['FlightCharacteristics' => ['Speeds' => ['Scm' => $speed]]],
            'name' => "Ship Speed {$speed}",
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=speed.scm');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('data.FlightCharacteristics.Speeds.Scm')->toArray();
    expect($returned)->toBe([150, 180, 220]);
});

it('sorts vehicles by shield face type alphabetically', function () {
    $faceTypes = ['Quad', 'Single', 'Dual'];

    foreach ($faceTypes as $faceType) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'data' => ['ShieldController' => ['FaceType' => $faceType]],
            'name' => "{$faceType} Shield",
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=shield.face_type');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('data.ShieldController.FaceType')->toArray();
    expect($returned)->toBe(['Dual', 'Quad', 'Single']); // Alphabetical
});

it('places null values last when sorting ascending', function () {
    // Create vehicles with cargo data
    foreach ([100, 200, 300] as $cargo) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'data' => ['Cargo' => $cargo],
            'name' => "Cargo {$cargo}",
        ]);
    }

    // Create vehicles without cargo data
    foreach (['No Cargo 1', 'No Cargo 2'] as $name) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'data' => [],
            'name' => $name,
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=cargo_capacity');

    $response->assertSuccessful();
    $data = collect($response->json('data'));

    // First 3 should have values, last 2 should be null
    expect($data->take(3)->every(fn ($item) => isset($item['data']['Cargo'])))->toBeTrue();
    expect($data->slice(3)->every(fn ($item) => ! isset($item['data']['Cargo'])))->toBeTrue();
});

it('places null values last when sorting descending', function () {
    // Create vehicles with health data
    foreach ([5000, 10000, 7500] as $health) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'data' => ['Health' => $health],
            'name' => "Health {$health}",
        ]);
    }

    // Create vehicles without health data
    foreach (['No Health 1', 'No Health 2'] as $name) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'data' => [],
            'name' => $name,
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=-health');

    $response->assertSuccessful();
    $data = collect($response->json('data'));

    // First 3 should have values (descending), last 2 should be null
    expect($data->take(3)->every(fn ($item) => isset($item['data']['Health'])))->toBeTrue();
    expect($data->slice(3)->every(fn ($item) => ! isset($item['data']['Health'])))->toBeTrue();
});

it('supports multiple field sorting', function () {
    $vehicle1 = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle1->id,
        'game_version_id' => $this->defaultVersion->id,
        'size' => 1,
        'name' => 'Zulu',
    ]);

    $vehicle2 = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle2->id,
        'game_version_id' => $this->defaultVersion->id,
        'size' => 1,
        'name' => 'Alpha',
    ]);

    $vehicle3 = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle3->id,
        'game_version_id' => $this->defaultVersion->id,
        'size' => 2,
        'name' => 'Charlie',
    ]);

    $vehicle4 = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle4->id,
        'game_version_id' => $this->defaultVersion->id,
        'size' => 2,
        'name' => 'Bravo',
    ]);

    $response = $this->getJson('/api/vehicles?sort=size,-name');

    $response->assertSuccessful();
    $items = collect($response->json('data'));

    // Should be: size 1 (Zulu, Alpha desc), size 2 (Charlie, Bravo desc)
    expect($items->pluck('name')->toArray())->toBe(['Zulu', 'Alpha', 'Charlie', 'Bravo']);
});

it('combines JSON sorting with filtering', function () {
    foreach ([100, 200, 150] as $cargo) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'is_spaceship' => true,
            'data' => ['Cargo' => $cargo],
            'name' => "Spaceship {$cargo}",
        ]);
    }

    foreach ([50, 75] as $cargo) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'is_vehicle' => true,
            'data' => ['Cargo' => $cargo],
            'name' => "Vehicle {$cargo}",
        ]);
    }

    $response = $this->getJson('/api/vehicles?filter[is_spaceship]=true&sort=-cargo_capacity');

    $response->assertSuccessful();
    expect($response->json('meta.total'))->toBe(3);

    $returned = collect($response->json('data'))->pluck('data.Cargo')->toArray();
    expect($returned)->toBe([200, 150, 100]);
});

it('works with pagination', function () {
    foreach (range(1, 15) as $i) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'size' => rand(1, 4),
            'name' => "Ship {$i}",
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=-size&page[size]=5&page[number]=1');

    $response->assertSuccessful();
    expect($response->json('meta.per_page'))->toBe(5);
    expect($response->json('meta.current_page'))->toBe(1);

    $sizes = collect($response->json('data'))->pluck('size')->toArray();
    expect($sizes)->toBe(collect($sizes)->sortDesc()->values()->toArray());
});

it('sorts by cross section dimensions', function () {
    $dimensions = [
        ['X' => 10, 'Y' => 5, 'Z' => 3],
        ['X' => 20, 'Y' => 10, 'Z' => 6],
        ['X' => 15, 'Y' => 7, 'Z' => 4],
    ];

    foreach ($dimensions as $idx => $dim) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'data' => ['CrossSection' => $dim],
            'name' => "Ship {$idx}",
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=cross_section.length');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('data.CrossSection.X')->toArray();
    expect($returned)->toBe([10, 15, 20]);
});

it('sorts by emission signature', function () {
    $emissions = [500, 1000, 750];

    foreach ($emissions as $em) {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->defaultVersion->id,
            'data' => ['Emission' => ['EmQuantum' => $em]],
            'name' => "Ship EM {$em}",
        ]);
    }

    $response = $this->getJson('/api/vehicles?sort=-signature.em_quantum');

    $response->assertSuccessful();
    $returned = collect($response->json('data'))->pluck('data.Emission.EmQuantum')->toArray();
    expect($returned)->toBe([1000, 750, 500]);
});
