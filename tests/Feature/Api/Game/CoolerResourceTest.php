<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;

uses(RefreshDatabase::class);

it('returns cooler specification when item type is cooler', function (): void {
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

    $response = $this->getJson("/api/items/{$item->uuid}");
    $coolerData = Arr::get($itemData->data, 'stdItem.Cooler', []);

    $response->assertSuccessful()
        ->assertJsonPath('data.cooler.cooling_rate', Arr::get($coolerData, 'CoolingRate'))
        ->assertJsonPath('data.cooler.suppression_ir_factor', Arr::get($coolerData, 'SuppressionIRFactor'))
        ->assertJsonPath('data.cooler.suppression_heat_factor', Arr::get($coolerData, 'SuppressionHeatFactor'))
        ->assertJsonPath('data.cooler.coolant_segment_generation', 22)
        ->assertJsonMissingPath('data.emp');
});
