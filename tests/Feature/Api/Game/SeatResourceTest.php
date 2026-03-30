<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns seat specification when item type is seat', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.4.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
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

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
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
    $version = GameVersion::factory()->create([
        'code' => '4.4.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
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

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.seat.seat_type', 'HOTAS_C_L')
        ->assertJsonPath('data.seat.yaw', null)
        ->assertJsonPath('data.seat.pitch', null)
        ->assertJsonPath('data.seat.set_yaw_pitch_limits', null)
        ->assertJsonPath('data.seat.has_ejection', false)
        ->assertJsonPath('data.seat.ejection', null);
});
