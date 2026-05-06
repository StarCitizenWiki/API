<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->version = GameVersion::factory()->create([
        'code' => 'test-version',
        'channel' => 'testing',
        'is_default' => true,
    ]);

    $this->manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);
});

describe('filter endpoint', function (): void {
    beforeEach(function (): void {
        app()->instance('env', 'production');
        app('cache')->setDefaultDriver('array');
        app()->forgetInstance('cache');
        app('cache')->forgetDriver(['array', 'database']);
        Cache::store('array')->flush();
    });

    it('returns filtered in-game vehicle facet values without caching the filtered response', function (): void {
        $destroyerManufacturer = Manufacturer::factory()->create([
            'name' => 'RSI',
            'code' => 'RSI',
        ]);
        $cargoManufacturer = Manufacturer::factory()->create([
            'name' => 'Drake',
            'code' => 'DRK',
        ]);

        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->for($destroyerManufacturer)
            ->create([
                'name' => 'Javelin',
                'career' => 'Destroyer',
                'role' => 'Capital Ship',
                'size' => 6,
                'data' => [
                    'ShieldController' => [
                        'FaceType' => 'Bubble',
                    ],
                ],
            ]);

        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->for($cargoManufacturer)
            ->create([
                'name' => 'Caterpillar',
                'career' => 'Cargo',
                'role' => 'Freighter',
                'size' => 5,
                'data' => [
                    'ShieldController' => [
                        'FaceType' => 'FrontBack',
                    ],
                ],
            ]);

        $response = $this->getJson(route('vehicles.filters', [
            'version' => $this->version->code,
            'filter' => ['career' => 'Destroyer'],
        ]));

        $response->assertOk()
            ->assertJsonPath('filters.manufacturer', [
                ['value' => 'RSI', 'label' => 'RSI', 'count' => 1],
            ])
            ->assertJsonPath('filters.career', [
                ['value' => 'Destroyer', 'label' => 'Destroyer', 'count' => 1],
            ])
            ->assertJsonPath('filters.role', [
                ['value' => 'Capital Ship', 'label' => 'Capital Ship', 'count' => 1],
            ]);

        expect(Cache::get('filters:index:vehicles'))->toBeNull();
    });
});

describe('query filter', function (): void {
    it('filters vehicles by query matching name', function (): void {
        $match = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($match)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Arrow',
                'class_name' => 'Anvil_Arrow',
                'data' => [],
            ]);

        $other = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($other)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Hornet',
                'class_name' => 'Anvil_Hornet',
                'data' => [],
            ]);

        $response = $this->getJson('/api/vehicles?filter[query]=Arrow');

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $match->uuid);
    });

    it('filters vehicles by query matching class_name', function (): void {
        $match = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($match)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Gladius',
                'class_name' => 'AEGS_Gladius',
                'data' => [],
            ]);

        $other = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($other)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Sabre',
                'class_name' => 'AEGS_Sabre',
                'data' => [],
            ]);

        $response = $this->getJson('/api/vehicles?filter[query]=AEGS_Gladius');

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $match->uuid);
    });

    it('returns empty when query matches nothing', function (): void {
        $vehicle = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($vehicle)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Existing Vehicle',
                'class_name' => 'TEST_Existing',
                'data' => [],
            ]);

        $response = $this->getJson('/api/vehicles?filter[query]=zzznonexistent');

        $response->assertSuccessful()
            ->assertJsonCount(0, 'data');
    });

    it('filters vehicles by json speed scm', function (): void {
        $matchingVehicle = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($matchingVehicle)
            ->for($this->version, 'gameVersion')
            ->create([
                'data' => [
                    'FlightCharacteristics' => [
                        'Speeds' => [
                            'Scm' => 123,
                        ],
                    ],
                ],
            ]);

        $otherVehicle = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($otherVehicle)
            ->for($this->version, 'gameVersion')
            ->create([
                'data' => [
                    'FlightCharacteristics' => [
                        'Speeds' => [
                            'Scm' => 200,
                        ],
                    ],
                ],
            ]);

        $response = $this->getJson('/api/vehicles?filter[speed.scm]=123');

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $matchingVehicle->uuid);
    })->group('db-pgsql');
});

describe('type filter', function (): void {
    it('filters ground-vehicles route to only ground vehicles', function (): void {
        $groundVehicle = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($groundVehicle)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'class_name' => 'GroundVehicle_Class',
                'name' => 'Ground Vehicle',
                'is_vehicle' => true,
                'is_gravlev' => false,
                'is_spaceship' => false,
                'data' => [],
            ]);

        $gravlevVehicle = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($gravlevVehicle)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'class_name' => 'GravlevVehicle_Class',
                'name' => 'Gravlev Vehicle',
                'is_vehicle' => false,
                'is_gravlev' => true,
                'is_spaceship' => false,
                'data' => [],
            ]);

        $spaceship = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($spaceship)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'class_name' => 'Spaceship_Class',
                'name' => 'Spaceship',
                'is_vehicle' => false,
                'is_gravlev' => false,
                'is_spaceship' => true,
                'data' => [],
            ]);

        $response = $this->getJson(route('ground-vehicles.index'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $groundVehicle->uuid);
    });

    it('filters gravlev-vehicles route to only gravlev vehicles', function (): void {
        $gravlevVehicle = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($gravlevVehicle)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'class_name' => 'GravlevVehicle_Class',
                'name' => 'Gravlev Vehicle',
                'is_vehicle' => false,
                'is_gravlev' => true,
                'is_spaceship' => false,
                'data' => [],
            ]);

        $groundVehicle = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($groundVehicle)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'class_name' => 'GroundVehicle_Class',
                'name' => 'Ground Vehicle',
                'is_vehicle' => true,
                'is_gravlev' => false,
                'is_spaceship' => false,
                'data' => [],
            ]);

        $response = $this->getJson(route('gravlev-vehicles.index'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $gravlevVehicle->uuid);
    });

    it('returns all vehicles on the main vehicles route', function (): void {
        $groundVehicle = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($groundVehicle)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'class_name' => 'GroundVehicle_Class',
                'name' => 'Ground Vehicle',
                'is_vehicle' => true,
                'is_gravlev' => false,
                'is_spaceship' => false,
                'data' => [],
            ]);

        $gravlevVehicle = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($gravlevVehicle)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'class_name' => 'GravlevVehicle_Class',
                'name' => 'Gravlev Vehicle',
                'is_vehicle' => false,
                'is_gravlev' => true,
                'is_spaceship' => false,
                'data' => [],
            ]);

        $spaceship = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($spaceship)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'class_name' => 'Spaceship_Class',
                'name' => 'Spaceship',
                'is_vehicle' => false,
                'is_gravlev' => false,
                'is_spaceship' => true,
                'data' => [],
            ]);

        $response = $this->getJson(route('vehicles.index'));

        $response->assertOk()
            ->assertJsonCount(3, 'data');

        $returnedUuids = collect($response->json('data'))
            ->pluck('uuid')
            ->sort()
            ->values()
            ->all();

        expect($returnedUuids)->toBe(
            collect([
                $groundVehicle->uuid,
                $gravlevVehicle->uuid,
                $spaceship->uuid,
            ])->sort()->values()->all()
        );
    });

    it('filters ground-vehicles search results correctly', function (): void {
        $groundVehicle = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($groundVehicle)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'class_name' => 'GroundVehicle_Class',
                'name' => 'Test Rover',
                'is_vehicle' => true,
                'is_gravlev' => false,
                'is_spaceship' => false,
                'data' => [],
            ]);

        $gravlevVehicle = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($gravlevVehicle)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'class_name' => 'GravlevVehicle_Class',
                'name' => 'Test Bike',
                'is_vehicle' => false,
                'is_gravlev' => true,
                'is_spaceship' => false,
                'data' => [],
            ]);

        $response = $this->postJson(route('ground-vehicles.search'), [
            'query' => 'Test',
        ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $groundVehicle->uuid);
    });

    it('filters gravlev-vehicles search results correctly', function (): void {
        $groundVehicle = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($groundVehicle)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'class_name' => 'GroundVehicle_Class',
                'name' => 'Test Rover',
                'is_vehicle' => true,
                'is_gravlev' => false,
                'is_spaceship' => false,
                'data' => [],
            ]);

        $gravlevVehicle = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($gravlevVehicle)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'class_name' => 'GravlevVehicle_Class',
                'name' => 'Test Bike',
                'is_vehicle' => false,
                'is_gravlev' => true,
                'is_spaceship' => false,
                'data' => [],
            ]);

        $response = $this->postJson(route('gravlev-vehicles.search'), [
            'query' => 'Test',
        ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $gravlevVehicle->uuid);
    });

    it('applies other filters alongside vehicle type filtering', function (): void {
        $manufacturer1 = Manufacturer::factory()->create([
            'name' => 'Manufacturer A',
            'code' => 'MFRA',
        ]);

        $manufacturer2 = Manufacturer::factory()->create([
            'name' => 'Manufacturer B',
            'code' => 'MFRB',
        ]);

        $groundVehicle1 = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($groundVehicle1)
            ->for($this->version, 'gameVersion')
            ->for($manufacturer1)
            ->create([
                'class_name' => 'GroundVehicle1_Class',
                'name' => 'Ground Vehicle A',
                'is_vehicle' => true,
                'is_gravlev' => false,
                'is_spaceship' => false,
                'data' => [],
            ]);

        $groundVehicle2 = Vehicle::factory()->create();
        VehicleData::factory()
            ->for($groundVehicle2)
            ->for($this->version, 'gameVersion')
            ->for($manufacturer2)
            ->create([
                'class_name' => 'GroundVehicle2_Class',
                'name' => 'Ground Vehicle B',
                'is_vehicle' => true,
                'is_gravlev' => false,
                'is_spaceship' => false,
                'data' => [],
            ]);

        $response = $this->getJson(route('ground-vehicles.index', [
            'filter' => ['manufacturer' => 'Manufacturer A'],
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $groundVehicle1->uuid);

        $response = $this->getJson(route('ground-vehicles.index', [
            'filter' => ['manufacturer' => 'MFRA'],
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $groundVehicle1->uuid);
    });
});
