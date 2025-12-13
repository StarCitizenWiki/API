<?php

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns cooler specification when item type is cooler', function () {
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

    $item = Item::create(['uuid' => 'test-cooler-uuid']);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Test Cooler',
        'class_name' => 'COOL_TEST_S01',
        'classification' => 'Ship.Cooler',
        'type' => 'Cooler',
        'sub_type' => 'UNDEFINED',
        'size' => 1,
        'data' => [
            'stdItem' => [
                'Cooler' => [
                    'CoolingRate' => 4080000,
                    'SuppressionIRFactor' => 0.1,
                    'SuppressionHeatFactor' => 0.1,
                ],
            ],
        ],
    ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.cooler.cooling_rate', 4080000)
        ->assertJsonPath('data.cooler.suppression_ir_factor', 0.1)
        ->assertJsonPath('data.cooler.suppression_heat_factor', 0.1);
});
