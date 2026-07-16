<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;

beforeEach(function (): void {
    $this->version = GameVersion::factory()->create([
        'code' => '4.4.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $this->manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);
});

describe('cooler specification', function (): void {
    it('returns cooler specification when item type is cooler', function (): void {
        $item = Item::factory()->create();

        ItemData::factory()
            ->for($item)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->cooler()
            ->create([
                'name' => 'Test Cooler',
                'class_name' => 'COOL_TEST_S01',
                'size' => 1,
                'data' => [
                    'stdItem' => [
                        'Cooler' => [
                            'CoolingRate' => 4080000,
                            'SuppressionIRFactor' => 0.1,
                            'SuppressionHeatFactor' => 0.2,
                        ],
                        'ResourceNetwork' => [
                            'Generation' => [
                                'Coolant' => 22,
                            ],
                        ],
                    ],
                ],
            ]);

        $this->getJson("/api/items/{$item->uuid}")
            ->assertSuccessful()
            ->assertJsonPath('data.cooler.cooling_rate', 4080000)
            ->assertJsonPath('data.cooler.suppression_ir_factor', 0.1)
            ->assertJsonPath('data.cooler.suppression_heat_factor', 0.2)
            ->assertJsonPath('data.cooler.coolant_segment_generation', 22)
            ->assertJsonMissingPath('data.emp');
    });
});

describe('emp specification', function (): void {
    it('returns emp specification when item type is emp', function (): void {
        $item = Item::factory()->create();

        ItemData::factory()
            ->for($item)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->emp()
            ->create([
                'name' => 'Test EMP',
                'class_name' => 'EMP_TEST_S01',
                'size' => 1,
                'data' => [
                    'stdItem' => [
                        'Emp' => [
                            'ChargeTime' => 12.0,
                            'DistortionDamage' => 1000.0,
                            'EmpRadius' => 400.0,
                            'MinEmpRadius' => 150.0,
                            'UnleashTime' => 0.75,
                            'CooldownTime' => 6.0,
                        ],
                    ],
                ],
            ]);

        $this->getJson("/api/items/{$item->uuid}")
            ->assertSuccessful()
            ->assertJsonPath('data.emp.distortion_damage', 1000)
            ->assertJsonPath('data.emp.emp_radius', 400)
            ->assertJsonPath('data.emp.min_emp_radius', 150)
            ->assertJsonPath('data.emp.charge_duration', 12)
            ->assertJsonPath('data.emp.unleash_duration', 0.75)
            ->assertJsonPath('data.emp.cooldown_duration', 6)
            ->assertJsonMissingPath('data.cooler');
    });
});

describe('seat specification', function (): void {
    it('returns seat specification when item type is seat', function (): void {
        $item = Item::factory()->create();

        ItemData::factory()
            ->for($item)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->seat()
            ->create([
                'name' => 'Test Seat',
                'class_name' => 'SEAT_TEST_S01',
                'size' => 1,
                'data' => [
                    'stdItem' => [
                        'Seat' => [
                            'SeatType' => 'HOTAS_C_L',
                            'Yaw' => [
                                'Minimum' => -70,
                                'Maximum' => 70,
                            ],
                            'Pitch' => [
                                'Minimum' => -65,
                                'Maximum' => 65,
                            ],
                            'SetYawPitchLimits' => false,
                            'HasEjection' => true,
                            'Ejection' => [
                                'MaxLinearVelocity' => 2000,
                                'MaxLinearAcceleration' => 100,
                                'MaxAngularVelocity' => 2000,
                                'MaxAngularAcceleration' => 100,
                                'EjectionLoopTime' => 1,
                            ],
                        ],
                    ],
                ],
            ]);

        $this->getJson("/api/items/{$item->uuid}")
            ->assertSuccessful()
            ->assertJsonPath('data.seat.seat_type', 'HOTAS_C_L')
            ->assertJsonPath('data.seat.yaw.minimum', -70)
            ->assertJsonPath('data.seat.yaw.maximum', 70)
            ->assertJsonPath('data.seat.pitch.minimum', -65)
            ->assertJsonPath('data.seat.pitch.maximum', 65)
            ->assertJsonPath('data.seat.set_yaw_pitch_limits', false)
            ->assertJsonPath('data.seat.has_ejection', true)
            ->assertJsonPath('data.seat.ejection.max_linear_velocity', 2000)
            ->assertJsonPath('data.seat.ejection.max_linear_acceleration', 100)
            ->assertJsonPath('data.seat.ejection.max_angular_velocity', 2000)
            ->assertJsonPath('data.seat.ejection.max_angular_acceleration', 100)
            ->assertJsonPath('data.seat.ejection.ejection_loop_time', 1);
    });

    it('returns null axis limits and ejection data when seat values are missing', function (): void {
        $item = Item::factory()->create();

        ItemData::factory()
            ->for($item)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->seat()
            ->create([
                'name' => 'Test Seat',
                'class_name' => 'SEAT_TEST_S01',
                'size' => 1,
                'data' => [
                    'stdItem' => [
                        'Seat' => [
                            'SeatType' => 'HOTAS_C_L',
                            'SetYawPitchLimits' => null,
                        ],
                    ],
                ],
            ]);

        $this->getJson("/api/items/{$item->uuid}")
            ->assertSuccessful()
            ->assertJsonPath('data.seat.seat_type', 'HOTAS_C_L')
            ->assertJsonPath('data.seat.yaw', null)
            ->assertJsonPath('data.seat.pitch', null)
            ->assertJsonPath('data.seat.has_ejection', false)
            ->assertJsonPath('data.seat.ejection', null);
    });
});

describe('food specification', function (): void {
    it('exposes resource and gas effects for food items', function (): void {
        $item = Item::factory()->create();

        ItemData::factory()
            ->for($item)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Snack',
                'class_name' => 'FOOD_TEST_01',
                'type' => 'Food',
                'sub_type' => 'UNDEFINED',
                'classification' => 'Food',
                'data' => [
                    'stdItem' => [
                        'Food' => [
                            'ResourceEffects' => [
                                ['consumableResourceType' => 'Water', 'perMicroSCU' => 0.5, 'total' => 1.25],
                            ],
                            'GasEffects' => [
                                ['gas' => 'Oxygen', 'mass' => 0.012],
                            ],
                        ],
                    ],
                ],
            ]);

        $this->getJson("/api/items/{$item->uuid}")
            ->assertSuccessful()
            ->assertJsonPath('data.food.resource_effects.0.consumable_resource_type', 'Water')
            ->assertJsonPath('data.food.resource_effects.0.per_micro_scu', 0.5)
            ->assertJsonPath('data.food.resource_effects.0.total', 1.25)
            ->assertJsonPath('data.food.gas_effects.0.gas', 'Oxygen')
            ->assertJsonPath('data.food.gas_effects.0.mass', 0.012);
    });

    it('returns null resource and gas effects when absent', function (): void {
        $item = Item::factory()->create();

        ItemData::factory()
            ->for($item)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Plain Water',
                'class_name' => 'FOOD_PLAIN_01',
                'type' => 'Food',
                'sub_type' => 'UNDEFINED',
                'classification' => 'Food',
                'data' => [
                    'stdItem' => [
                        'Food' => [],
                    ],
                ],
            ]);

        $this->getJson("/api/items/{$item->uuid}")
            ->assertSuccessful()
            ->assertJsonPath('data.food.resource_effects', null)
            ->assertJsonPath('data.food.gas_effects', null);
    });
});

describe('medicine specification', function (): void {
    it('exposes inherited resource effects for medical items', function (): void {
        // Medical items carry @Type=Food / @SubType=Medical, so both the food and
        // medical predicates match. Only stdItem.Medical is populated (Food returns
        // null for medical subtypes), so data.food renders empty and data.medical
        // carries the inherited resource/gas effects from the shared consumable data.
        $item = Item::factory()->create();

        ItemData::factory()
            ->for($item)
            ->for($this->version, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test MedPen',
                'class_name' => 'MED_TEST_01',
                'type' => 'Food',
                'sub_type' => 'Medical',
                'classification' => 'Medicine',
                'data' => [
                    'stdItem' => [
                        'Medical' => [
                            'ResourceEffects' => [
                                ['consumableResourceType' => 'BloodDrugLevel', 'perMicroSCU' => 0.75, 'total' => 22.5],
                            ],
                        ],
                    ],
                ],
            ]);

        $this->getJson("/api/items/{$item->uuid}")
            ->assertSuccessful()
            ->assertJsonPath('data.medical.resource_effects.0.consumable_resource_type', 'BloodDrugLevel')
            ->assertJsonPath('data.medical.resource_effects.0.per_micro_scu', 0.75)
            ->assertJsonPath('data.medical.resource_effects.0.total', 22.5)
            ->assertJsonPath('data.medical.gas_effects', null);
    });
});
