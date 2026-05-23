<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;

beforeEach(function () {
    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $this->manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);
});

describe('new dimension blocks', function () {
    it('exposes all three dimension blocks when present', function (): void {
        $item = Item::factory()->create();

        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Full Dimension Item',
                'type' => 'PowerPlant',
                'class_name' => 'full_dim_item',
                'classification' => 'Test.Module',
                'data' => [
                    'stdItem' => [
                        'InventoryOccupancy' => [
                            'Dimensions' => [
                                'Width' => 0.458,
                                'Height' => 0.354,
                                'Length' => 0.466,
                            ],
                            'CargoGrid' => [
                                'Width' => 0.24,
                                'Height' => 0.34,
                                'Length' => 0.30,
                            ],
                            'UIDimensions' => [
                                'Width' => 0.75,
                                'Height' => 0.75,
                                'Length' => 0.75,
                            ],
                            'Volume' => [
                                'SCU' => 0.019,
                                'SCUConverted' => 19000,
                                'Unit' => 'µSCU',
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->getJson("/api/items/{$item->uuid}");

        $response->assertSuccessful();

        $dim = $response->json('data.dimension');

        // New blocks
        expect($dim['dimensions'])->toBe([
            'width' => 0.458,
            'height' => 0.354,
            'length' => 0.466,
        ])
            ->and($dim['cargo_dimension'])->toBe([
                'width' => 0.24,
                'height' => 0.34,
                'length' => 0.30,
            ])
            ->and($dim['ui_dimension'])->toBe([
                'width' => 0.75,
                'height' => 0.75,
                'length' => 0.75,
            ]);

        // Volume stays shared
        expect($dim['volume'])->toBe(0.019)
            ->and($dim['volume_converted'])->toBe(19000)
            ->and($dim['volume_converted_unit'])->toBe('µSCU');
    });

    it('omits cargo_dimension when CargoGrid is absent', function (): void {
        $item = Item::factory()->create();

        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'No Cargo Grid Item',
                'type' => 'PowerPlant',
                'class_name' => 'no_cargo_item',
                'classification' => 'Test.Module',
                'data' => [
                    'stdItem' => [
                        'InventoryOccupancy' => [
                            'Dimensions' => [
                                'Width' => 1.0,
                                'Height' => 2.0,
                                'Length' => 3.0,
                            ],
                            'Volume' => [
                                'SCUConverted' => 0.5,
                                'Unit' => 'SCU',
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->getJson("/api/items/{$item->uuid}");

        $response->assertSuccessful();

        $dim = $response->json('data.dimension');

        expect($dim)->toHaveKey('dimensions')
            ->and($dim)->not->toHaveKey('cargo_dimension')
            ->and($dim)->not->toHaveKey('ui_dimension');
    });

    it('omits ui_dimension when UIDimensions is absent', function (): void {
        $item = Item::factory()->create();

        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'No UI Override Item',
                'type' => 'PowerPlant',
                'class_name' => 'no_ui_item',
                'classification' => 'Test.Module',
                'data' => [
                    'stdItem' => [
                        'InventoryOccupancy' => [
                            'CargoGrid' => [
                                'Width' => 0.24,
                                'Height' => 0.34,
                                'Length' => 0.30,
                            ],
                            'Volume' => [
                                'SCU' => 0.019,
                                'SCUConverted' => 19000,
                                'Unit' => 'µSCU',
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->getJson("/api/items/{$item->uuid}");

        $response->assertSuccessful();

        $dim = $response->json('data.dimension');

        expect($dim)->not->toHaveKey('dimensions')
            ->and($dim)->toHaveKey('cargo_dimension')
            ->and($dim)->not->toHaveKey('ui_dimension');
    });

    it('omits dimensions block when Dimensions is absent', function (): void {
        $item = Item::factory()->create();

        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Cargo Only Item',
                'type' => 'PowerPlant',
                'class_name' => 'cargo_only_item',
                'classification' => 'Test.Module',
                'data' => [
                    'stdItem' => [
                        'InventoryOccupancy' => [
                            'CargoGrid' => [
                                'Width' => 0.24,
                                'Height' => 0.34,
                                'Length' => 0.30,
                            ],
                            'UIDimensions' => [
                                'Width' => 0.75,
                                'Height' => 0.75,
                                'Length' => 0.75,
                            ],
                            'Volume' => [
                                'SCU' => 0.019,
                                'SCUConverted' => 19000,
                                'Unit' => 'µSCU',
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->getJson("/api/items/{$item->uuid}");

        $response->assertSuccessful();

        $dim = $response->json('data.dimension');

        expect($dim)->not->toHaveKey('dimensions')
            ->and($dim)->toHaveKey('cargo_dimension')
            ->and($dim)->toHaveKey('ui_dimension');
    });
});

describe('backwards compat', function () {
    it('keeps deprecated flat width/height/length preferring UI dims when they differ', function (): void {
        $item = Item::factory()->create();

        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Override Test Item',
                'type' => 'PowerPlant',
                'class_name' => 'override_test_item',
                'classification' => 'Test.Module',
                'data' => [
                    'stdItem' => [
                        'InventoryOccupancy' => [
                            'Dimensions' => [
                                'Width' => 1.0,
                                'Height' => 2.0,
                                'Length' => 3.0,
                            ],
                            'UIDimensions' => [
                                'Width' => 1.5,
                                'Height' => 2.5,
                                'Length' => 3.5,
                            ],
                            'Volume' => [
                                'SCUConverted' => 0.5,
                                'Unit' => 'SCU',
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->getJson("/api/items/{$item->uuid}");

        $response->assertSuccessful();

        $dim = $response->json('data.dimension');

        // Flat fields prefer UIDimensions
        expect($dim['width'])->toBe(1.5)
            ->and($dim['height'])->toBe(2.5)
            ->and($dim['length'])->toBe(3.5);

        // true_dimension shows the original Dimensions
        expect($dim['true_dimension']['width'])->toBeIn([1, 1.0])
            ->and($dim['true_dimension']['height'])->toBeIn([2, 2.0])
            ->and($dim['true_dimension']['length'])->toBeIn([3, 3.0]);
    });

    it('keeps deprecated flat fields using Dimensions when no UIDimensions', function (): void {
        $item = Item::factory()->create();

        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'No Override Item',
                'type' => 'PowerPlant',
                'class_name' => 'no_override_item',
                'classification' => 'Test.Module',
                'data' => [
                    'stdItem' => [
                        'InventoryOccupancy' => [
                            'Dimensions' => [
                                'Width' => 1.0,
                                'Height' => 2.0,
                                'Length' => 3.0,
                            ],
                            'Volume' => [
                                'SCUConverted' => 0.5,
                                'Unit' => 'SCU',
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->getJson("/api/items/{$item->uuid}");

        $response->assertSuccessful();

        $dim = $response->json('data.dimension');

        expect($dim['width'])->toBeIn([1, 1.0])
            ->and($dim['height'])->toBeIn([2, 2.0])
            ->and($dim['length'])->toBeIn([3, 3.0])
            ->and($dim)->not->toHaveKey('true_dimension');
    });

    it('omits true_dimension when UI dims equal true dims', function (): void {
        $item = Item::factory()->create();

        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Equal Dims Item',
                'type' => 'PowerPlant',
                'class_name' => 'equal_dims_item',
                'classification' => 'Test.Module',
                'data' => [
                    'stdItem' => [
                        'InventoryOccupancy' => [
                            'Dimensions' => [
                                'Width' => 1.0,
                                'Height' => 2.0,
                                'Length' => 3.0,
                            ],
                            'UIDimensions' => [
                                'Width' => 1.0,
                                'Height' => 2.0,
                                'Length' => 3.0,
                            ],
                            'Volume' => [
                                'SCUConverted' => 0.5,
                                'Unit' => 'SCU',
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->getJson("/api/items/{$item->uuid}");

        $response->assertSuccessful();

        $dim = $response->json('data.dimension');

        expect($dim)->not->toHaveKey('true_dimension');
    });
});
