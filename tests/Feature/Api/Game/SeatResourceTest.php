<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;

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

    $itemData = ItemData::factory()
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
                        'HasEjection' => false,
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

    $seatData = Arr::get($itemData->data, 'stdItem.Seat', []);

    $response->assertSuccessful()
        ->assertJsonPath('data.seat.seat_type', Arr::get($seatData, 'SeatType'))
        ->assertJsonPath('data.seat.yaw.minimum', Arr::get($seatData, 'Yaw.Minimum'))
        ->assertJsonPath('data.seat.yaw.maximum', Arr::get($seatData, 'Yaw.Maximum'))
        ->assertJsonPath('data.seat.pitch.minimum', Arr::get($seatData, 'Pitch.Minimum'))
        ->assertJsonPath('data.seat.pitch.maximum', Arr::get($seatData, 'Pitch.Maximum'))
        ->assertJsonPath('data.seat.set_yaw_pitch_limits', Arr::get($seatData, 'SetYawPitchLimits'))
        ->assertJsonPath('data.seat.has_ejection', true)
        ->assertJsonPath('data.seat.ejection.max_linear_velocity', Arr::get($seatData, 'Ejection.MaxLinearVelocity'))
        ->assertJsonPath('data.seat.ejection.max_linear_acceleration', Arr::get($seatData, 'Ejection.MaxLinearAcceleration'))
        ->assertJsonPath('data.seat.ejection.max_angular_velocity', Arr::get($seatData, 'Ejection.MaxAngularVelocity'))
        ->assertJsonPath('data.seat.ejection.max_angular_acceleration', Arr::get($seatData, 'Ejection.MaxAngularAcceleration'))
        ->assertJsonPath('data.seat.ejection.ejection_loop_time', Arr::get($seatData, 'Ejection.EjectionLoopTime'));
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
        ->assertJsonPath('data.seat.has_ejection', false)
        ->assertJsonPath('data.seat.ejection', null);
});
