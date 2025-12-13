<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns emp specification when item type is emp', function (): void {
    $version = GameVersion::create([
        'code' => '4.4.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::create([
        'uuid' => 'test-manufacturer-uuid',
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $item = Item::create(['uuid' => 'test-emp-uuid']);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Test EMP',
        'class_name' => 'EMP_TEST_S01',
        'classification' => 'Ship.EMP',
        'type' => 'EMP',
        'sub_type' => 'UNDEFINED',
        'size' => 1,
        'data' => [
            'stdItem' => [
                'Emp' => [
                    'ChargeTime' => 12,
                    'DistortionDamage' => 1000,
                    'EmpRadius' => 400,
                    'MinEmpRadius' => 150,
                    'PhysRadius' => 250,
                    'MinPhysRadius' => 150,
                    'Pressure' => 0,
                    'UnleashTime' => 0.75,
                    'CooldownTime' => 6,
                    'ChargingTag' => 'charging-tag-uuid',
                    'ChargedTag' => 'charged-tag-uuid',
                    'StartChargingTrigger' => 'start-charging-trigger',
                    'StopChargingTrigger' => 'stop-charging-trigger',
                    'StartChargedTrigger' => 'start-charged-trigger',
                    'StopChargedTrigger' => 'stop-charged-trigger',
                    'StartUnleashTrigger' => 'start-unleash-trigger',
                    'StopUnleashTrigger' => 'stop-unleash-trigger',
                    'IdleState' => 'states',
                    'ChargingState' => 'states',
                    'ChargedState' => 'states',
                    'ReleasingState' => 'states',
                ],
            ],
        ],
    ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.emp.charge_time', 12)
        ->assertJsonPath('data.emp.emp_radius', 400)
        ->assertJsonPath('data.emp.cooldown_time', 6)
        ->assertJsonPath('data.emp.charged_tag', 'charged-tag-uuid')
        ->assertJsonPath('data.emp.releasing_state', 'states');
});
