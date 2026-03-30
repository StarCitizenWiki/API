<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;

uses(RefreshDatabase::class);

it('returns emp specification when item type is emp', function (): void {
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

    $response = $this->getJson("/api/items/{$item->uuid}");

    $empData = Arr::get($itemData->data, 'stdItem.Emp', []);

    $response->assertSuccessful()
        ->assertJsonPath('data.emp.charge_duration', Arr::get($empData, 'ChargeTime'))
        ->assertJsonPath('data.emp.emp_radius', Arr::get($empData, 'EmpRadius'))
        ->assertJsonPath('data.emp.cooldown_duration', Arr::get($empData, 'CooldownTime'))
        ->assertJsonPath('data.emp.distortion_damage', Arr::get($empData, 'DistortionDamage'))
        ->assertJsonPath('data.emp.min_emp_radius', Arr::get($empData, 'MinEmpRadius'))
        ->assertJsonPath('data.emp.unleash_duration', Arr::get($empData, 'UnleashTime'))
        ->assertJsonMissingPath('data.cooler');
});
