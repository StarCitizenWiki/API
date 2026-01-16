<?php

declare(strict_types=1);

use App\Models\Game\EntityTag;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\ItemDescriptionData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the item show view with api data', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Acme Works',
        'code' => 'ACME',
    ]);

    $item = Item::factory()->create([
        'translation' => ['en' => 'Base item description'],
    ]);

    $itemData = ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Test Module',
            'class_name' => 'test_module',
            'classification' => 'Test.Module',
            'type' => 'PowerPlant',
            'sub_type' => 'Small',
            'size' => 2,
            'data' => [
                'stdItem' => [
                    'Mass' => 12.5,
                    'InventoryOccupancy' => [
                        'Dimensions' => [
                            'Width' => 1.1,
                            'Height' => 2.2,
                            'Length' => 3.3,
                        ],
                        'Volume' => [
                            'SCUConverted' => 0.75,
                            'Unit' => 'SCU',
                        ],
                    ],
                    'Ports' => [
                        [
                            'PortName' => 'MainPort',
                            'DisplayName' => 'Main Port',
                            'Position' => 'Top',
                            'Size' => 2,
                            'Types' => ['Weapon'],
                            'Tags' => ['Test'],
                            'RequiredTags' => ['Core'],
                            'Flags' => ['Locked'],
                            'Uneditable' => true,
                        ],
                    ],
                ],
            ],
        ]);

    ItemDescriptionData::factory()->for($item)->create([
        'name' => 'Manufacturer',
        'value' => 'Acme Works',
    ]);

    $tag = EntityTag::factory()->create([
        'name' => 'Explosive',
    ]);

    $itemData->entityTags()->attach($tag->id);

    $variant = Item::factory()->create([
        'translation' => ['en' => 'Variant item description'],
    ]);

    ItemData::factory()
        ->for($variant)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Test Module Variant',
            'class_name' => 'test_module_variant',
            'classification' => 'Test.Module',
            'type' => 'PowerPlant',
            'sub_type' => 'Small',
            'base_id' => $itemData->id,
            'data' => [],
        ]);

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk()
        ->assertViewIs('items.show')
        ->assertSee('Test Module')
        ->assertSee('Main Port')
        ->assertSee('Test Module Variant')
        ->assertSee('Base Item')
        ->assertSee('Explosive')
        ->assertSee('Specifications')
        ->assertSee('All Data');
});
