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
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $empData = Arr::get($itemData->data, 'stdItem.Emp', []);

    $response->assertSuccessful()
        ->assertJsonPath('data.emp.charge_time', Arr::get($empData, 'ChargeTime'))
        ->assertJsonPath('data.emp.emp_radius', Arr::get($empData, 'EmpRadius'))
        ->assertJsonPath('data.emp.cooldown_time', Arr::get($empData, 'CooldownTime'))
        ->assertJsonPath('data.emp.distortion_damage', Arr::get($empData, 'DistortionDamage'));
});
