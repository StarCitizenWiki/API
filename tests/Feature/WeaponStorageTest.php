<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;

beforeEach(function () {
    $this->manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.4.0-TEST',
        'channel' => 'test',
        'is_default' => true,
        'released_at' => now(),
    ]);
});

describe('API: weapon_storage', function () {
    it('includes weapon_storage on show route when lockers exist', function (): void {
        $vehicle = Vehicle::factory()->create();

        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Carrack',
                'class_name' => 'TEST_Carrack',
                'data' => [
                    'WeaponStorage' => [
                        'Lockers' => 2,
                        'SlotsTotal' => 40,
                        'SlotsRifle' => 16,
                        'SlotsPistol' => 24,
                        'ByLocker' => [
                            [
                                'Name' => 'Weapon Rack',
                                'ClassName' => 'weapon_rack_test_armory_large',
                                'Port' => 'hardpoint_weapon_locker_01',
                                'SlotsTotal' => 20,
                                'SlotsRifle' => 8,
                                'SlotsPistol' => 12,
                            ],
                            [
                                'Name' => 'Weapon Rack',
                                'ClassName' => 'weapon_rack_test_armory_large',
                                'Port' => 'hardpoint_weapon_locker_02',
                                'SlotsTotal' => 20,
                                'SlotsRifle' => 8,
                                'SlotsPistol' => 12,
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->getJson("/api/v3/vehicles/{$vehicle->uuid}");

        $response->assertSuccessful()
            ->assertJsonPath('data.weapon_storage.lockers', 2)
            ->assertJsonPath('data.weapon_storage.slots_total', 40)
            ->assertJsonPath('data.weapon_storage.slots_rifle', 16)
            ->assertJsonPath('data.weapon_storage.slots_pistol', 24)
            ->assertJsonCount(1, 'data.weapon_storage.by_locker')
            ->assertJsonPath('data.weapon_storage.by_locker.0.name', 'Weapon Rack')
            ->assertJsonPath('data.weapon_storage.by_locker.0.port', 'hardpoint_weapon_locker_01')
            ->assertJsonPath('data.weapon_storage.by_locker.0.count', 2);
    });

    it('excludes weapon_storage when no lockers exist', function (): void {
        $vehicle = Vehicle::factory()->create();

        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Ship',
                'class_name' => 'TEST_Ship',
                'data' => [
                    'WeaponStorage' => [
                        'Lockers' => 0,
                        'SlotsTotal' => 0,
                        'SlotsRifle' => 0,
                        'SlotsPistol' => 0,
                        'ByLocker' => [],
                    ],
                ],
            ]);

        $response = $this->getJson("/api/v3/vehicles/{$vehicle->uuid}");

        $response->assertSuccessful()
            ->assertJsonMissingPath('data.weapon_storage');
    });

    it('excludes weapon_storage when WeaponStorage key is absent', function (): void {
        $vehicle = Vehicle::factory()->create();

        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Ship',
                'class_name' => 'TEST_Ship',
                'data' => [],
            ]);

        $response = $this->getJson("/api/v3/vehicles/{$vehicle->uuid}");

        $response->assertSuccessful()
            ->assertJsonMissingPath('data.weapon_storage');
    });

    it('includes weapon_storage on index route when lockers exist', function (): void {
        $vehicle = Vehicle::factory()->create();

        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Ship',
                'class_name' => 'TEST_Ship',
                'data' => [
                    'WeaponStorage' => [
                        'Lockers' => 1,
                        'SlotsTotal' => 10,
                        'SlotsRifle' => 5,
                        'SlotsPistol' => 5,
                        'ByLocker' => [
                            [
                                'Name' => 'Weapon Rack',
                                'ClassName' => 'weapon_rack_test',
                                'Port' => 'hardpoint_weapon_locker',
                                'SlotsTotal' => 10,
                                'SlotsRifle' => 5,
                                'SlotsPistol' => 5,
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->getJson('/api/v3/vehicles');

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.weapon_storage.lockers', 1)
            ->assertJsonPath('data.0.weapon_storage.slots_total', 10);
    });
});

describe('API: suit_storage', function () {
    it('includes suit_storage on show route when lockers exist', function (): void {
        $vehicle = Vehicle::factory()->create();

        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Cutter Rambler',
                'class_name' => 'drak_cutter_rambler',
                'data' => [
                    'SuitStorage' => [
                        'Lockers' => 1,
                        'SlotsTotal' => 8,
                        'ByLocker' => [
                            [
                                'Name' => '<= PLACEHOLDER =>',
                                'ClassName' => 'locker_suit_drak_cutter_rambler',
                                'Port' => 'hardpoint_suit_locker_expo',
                                'SlotsTotal' => 8,
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->getJson("/api/v3/vehicles/{$vehicle->uuid}");

        $response->assertSuccessful()
            ->assertJsonPath('data.suit_storage.lockers', 1)
            ->assertJsonPath('data.suit_storage.slots_total', 8)
            ->assertJsonCount(1, 'data.suit_storage.by_locker')
            ->assertJsonPath('data.suit_storage.by_locker.0.count', 1)
            ->assertJsonPath('data.suit_storage.by_locker.0.slots_total', 8)
            ->assertJsonPath('data.suit_storage.by_locker.0.class_name', 'locker_suit_drak_cutter_rambler');
    });

    it('groups identical suit lockers', function (): void {
        $vehicle = Vehicle::factory()->create();

        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Ship',
                'class_name' => 'TEST_Ship',
                'data' => [
                    'SuitStorage' => [
                        'Lockers' => 3,
                        'SlotsTotal' => 3,
                        'ByLocker' => [
                            [
                                'Name' => '<= PLACEHOLDER =>',
                                'ClassName' => 'cargo_slot_test',
                                'Port' => 'hardpoint_cargo_01',
                                'SlotsTotal' => 1,
                            ],
                            [
                                'Name' => '<= PLACEHOLDER =>',
                                'ClassName' => 'cargo_slot_test',
                                'Port' => 'hardpoint_cargo_02',
                                'SlotsTotal' => 1,
                            ],
                            [
                                'Name' => '<= PLACEHOLDER =>',
                                'ClassName' => 'cargo_slot_test',
                                'Port' => 'hardpoint_cargo_03',
                                'SlotsTotal' => 1,
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->getJson("/api/v3/vehicles/{$vehicle->uuid}");

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data.suit_storage.by_locker')
            ->assertJsonPath('data.suit_storage.by_locker.0.count', 3)
            ->assertJsonPath('data.suit_storage.by_locker.0.slots_total', 1);
    });

    it('excludes suit_storage when no lockers exist', function (): void {
        $vehicle = Vehicle::factory()->create();

        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Ship',
                'class_name' => 'TEST_Ship',
                'data' => [
                    'SuitStorage' => [
                        'Lockers' => 0,
                        'SlotsTotal' => 0,
                        'ByLocker' => [],
                    ],
                ],
            ]);

        $response = $this->getJson("/api/v3/vehicles/{$vehicle->uuid}");

        $response->assertSuccessful()
            ->assertJsonMissingPath('data.suit_storage');
    });
});

describe('Web card', function () {
    it('renders storage card with weapon and suit sections', function (): void {
        $vehicle = Vehicle::factory()->create([
            'uuid' => 'b0000000-0000-0000-0000-000000000001',
        ]);

        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Ship',
                'display_name' => 'Test Ship',
                'class_name' => 'TEST_Ship',
                'data' => [
                    'WeaponStorage' => [
                        'Lockers' => 2,
                        'SlotsTotal' => 40,
                        'SlotsRifle' => 16,
                        'SlotsPistol' => 24,
                        'ByLocker' => [
                            [
                                'Name' => 'Weapon Rack',
                                'ClassName' => 'weapon_rack_test',
                                'Port' => 'hardpoint_weapon_locker_01',
                                'SlotsTotal' => 20,
                                'SlotsRifle' => 8,
                                'SlotsPistol' => 12,
                            ],
                            [
                                'Name' => 'Weapon Rack',
                                'ClassName' => 'weapon_rack_test',
                                'Port' => 'hardpoint_weapon_locker_02',
                                'SlotsTotal' => 20,
                                'SlotsRifle' => 8,
                                'SlotsPistol' => 12,
                            ],
                        ],
                    ],
                    'SuitStorage' => [
                        'Lockers' => 1,
                        'SlotsTotal' => 8,
                        'ByLocker' => [
                            [
                                'Name' => '<= PLACEHOLDER =>',
                                'ClassName' => 'locker_suit_test',
                                'Port' => 'hardpoint_suit_locker',
                                'SlotsTotal' => 8,
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->get(route('web.vehicles.show', $vehicle->slug));

        $response->assertStatus(200)
            ->assertSeeText('Storage')
            ->assertSeeText('Weapon Storage')
            ->assertSeeText('Suit Storage')
            ->assertSeeText('Total Slots');
    });

    it('renders card with weapon storage only', function (): void {
        $vehicle = Vehicle::factory()->create([
            'uuid' => 'b0000000-0000-0000-0000-000000000002',
        ]);

        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Ship',
                'display_name' => 'Test Ship',
                'class_name' => 'TEST_Ship',
                'data' => [
                    'WeaponStorage' => [
                        'Lockers' => 2,
                        'SlotsTotal' => 40,
                        'SlotsRifle' => 16,
                        'SlotsPistol' => 24,
                        'ByLocker' => [
                            [
                                'Name' => 'Weapon Rack',
                                'ClassName' => 'weapon_rack_test',
                                'Port' => 'hardpoint_weapon_locker_01',
                                'SlotsTotal' => 20,
                                'SlotsRifle' => 8,
                                'SlotsPistol' => 12,
                            ],
                            [
                                'Name' => 'Weapon Rack',
                                'ClassName' => 'weapon_rack_test',
                                'Port' => 'hardpoint_weapon_locker_02',
                                'SlotsTotal' => 20,
                                'SlotsRifle' => 8,
                                'SlotsPistol' => 12,
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->get(route('web.vehicles.show', $vehicle->slug));

        $response->assertStatus(200)
            ->assertSeeText('Weapon Storage')
            ->assertDontSeeText('Suit Storage');
    });

    it('does not render storage card when no lockers', function (): void {
        $vehicle = Vehicle::factory()->create([
            'uuid' => 'b0000000-0000-0000-0000-000000000003',
        ]);

        VehicleData::factory()
            ->for($vehicle)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Ship',
                'display_name' => 'Test Ship',
                'class_name' => 'TEST_Ship',
                'data' => [
                    'WeaponStorage' => [
                        'Lockers' => 0,
                        'SlotsTotal' => 0,
                        'SlotsRifle' => 0,
                        'SlotsPistol' => 0,
                        'ByLocker' => [],
                    ],
                ],
            ]);

        $response = $this->get(route('web.vehicles.show', $vehicle->slug));

        $response->assertStatus(200)
            ->assertDontSeeText('Weapon Storage');
    });
});
