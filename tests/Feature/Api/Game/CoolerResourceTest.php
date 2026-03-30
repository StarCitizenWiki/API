<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

    ItemData::factory()
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

    $response->assertSuccessful()
        ->assertJsonPath('data.cooler.cooling_rate', 4080000)
        ->assertJsonPath('data.cooler.suppression_ir_factor', 0.1)
        ->assertJsonPath('data.cooler.suppression_heat_factor', 0.2)
        ->assertJsonPath('data.cooler.coolant_segment_generation', 22)
        ->assertJsonMissingPath('data.emp');
});
