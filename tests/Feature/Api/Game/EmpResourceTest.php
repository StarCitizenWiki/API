<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

    ItemData::factory()
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

    $response->assertSuccessful()
        ->assertJsonPath('data.emp.distortion_damage', 1000)
        ->assertJsonPath('data.emp.emp_radius', 400)
        ->assertJsonPath('data.emp.min_emp_radius', 150)
        ->assertJsonPath('data.emp.charge_duration', 12)
        ->assertJsonPath('data.emp.unleash_duration', 0.75)
        ->assertJsonPath('data.emp.cooldown_duration', 6)
        ->assertJsonMissingPath('data.cooler');
});
