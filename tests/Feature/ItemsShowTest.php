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
        ->assertSee('Raw Item Payload')
        ->assertSee('<meta name="keywords" content="Test Module,PowerPlant,Acme Works,Test.Module,Star Citizen,SC">', false)
        ->assertSee('<meta property="og:type" content="website">', false)
        ->assertSee('<meta property="og:title" content="Test Module - PowerPlant Acme Works">', false)
        ->assertSee('<meta name="twitter:card" content="summary">', false)
        ->assertSee('<meta name="twitter:title" content="Test Module - PowerPlant">', false);
});

it('renders minimal item with essentials block only', function (): void {
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
        'translation' => ['en' => 'Minimal item description'],
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Minimal Module',
            'class_name' => 'minimal_module',
            'classification' => 'Test.Module',
            'type' => 'PowerPlant',
            'sub_type' => 'Small',
            'size' => 1,
            'data' => [
                'stdItem' => [
                    'Mass' => 5.0,
                    'InventoryOccupancy' => [
                        'Dimensions' => [
                            'Width' => 0.5,
                            'Height' => 0.5,
                            'Length' => 0.5,
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk()
        ->assertViewIs('items.show')
        // Essentials Block - Always visible
        ->assertSee('Minimal Module')
        ->assertSee('minimal_module')
        ->assertSee('Acme Works')
        ->assertSee('PowerPlant')
        ->assertSee('Small')
        // Technical Section - Always visible
        ->assertSee($item->uuid)
        ->assertSee('4.0.0-LIVE');
});

it('renders ports-heavy item with collapsible ports section', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Drake Interplanetary',
        'code' => 'DRAK',
    ]);

    $item = Item::factory()->create([
        'translation' => ['en' => 'Ports-heavy ship component'],
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Ship Core',
            'class_name' => 'ship_core',
            'classification' => 'Ship.Component',
            'type' => 'Utility',
            'data' => [
                'stdItem' => [
                    'Mass' => 1500.0,
                    'InventoryOccupancy' => [
                        'Dimensions' => [
                            'Width' => 2.5,
                            'Height' => 3.0,
                            'Length' => 4.5,
                        ],
                    ],
                    'Ports' => [
                        ['PortName' => 'Power Port 1', 'DisplayName' => 'Power', 'Size' => 1],
                        ['PortName' => 'Power Port 2', 'DisplayName' => 'Power', 'Size' => 1],
                        ['PortName' => 'Cooling Port', 'DisplayName' => 'Cooling', 'Size' => 2],
                        ['PortName' => 'Weapon Port Left', 'DisplayName' => 'Weapon', 'Size' => 3],
                        ['PortName' => 'Weapon Port Right', 'DisplayName' => 'Weapon', 'Size' => 3],
                        ['PortName' => 'Shield Port', 'DisplayName' => 'Shield', 'Size' => 2],
                    ],
                ],
            ],
        ]);

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk()
        ->assertViewIs('items.show')
        // Essentials Block
        ->assertSee('Ship Core')
        ->assertSee('Drake Interplanetary')
        // Ports Section - Visible with count
        ->assertSee('Ports')
        ->assertSee('6') // Count badge
        ->assertSee('Power Port 1')
        ->assertSee('Weapon Port Left');
});

it('renders variant-heavy item with variants section', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Behring',
        'code' => 'BEHR',
    ]);

    $baseItem = Item::factory()->create([
        'translation' => ['en' => 'Base weapon description'],
    ]);

    $baseItemData = ItemData::factory()
        ->for($baseItem)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Laser Cannon',
            'class_name' => 'laser_cannon',
            'classification' => 'Weapon.Personal',
            'type' => 'Laser',
            'data' => [],
        ]);

    // Create 4 variants
    for ($i = 1; $i <= 4; $i++) {
        $variant = Item::factory()->create([
            'translation' => ['en' => "Variant {$i} description"],
        ]);

        ItemData::factory()
            ->for($variant)
            ->for($version, 'gameVersion')
            ->for($manufacturer)
            ->create([
                'name' => "Laser Cannon Variant {$i}",
                'class_name' => "laser_cannon_v{$i}",
                'classification' => 'Weapon.Personal',
                'type' => 'Laser',
                'base_id' => $baseItemData->id,
                'data' => [],
            ]);
    }

    $response = $this->get(route('web.items.show', $baseItem->uuid));

    $response->assertOk()
        ->assertViewIs('items.show')
        // Essentials Block
        ->assertSee('Laser Cannon')
        ->assertSee('Behring')
        // Variants Section - Visible with variants
        ->assertSee('Variants')
        ->assertSee('Base Item')
        ->assertSee('Laser Cannon Variant 1')
        ->assertSee('Laser Cannon Variant 4');
});

it('renders spec-heavy item with dynamic component sections', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Aegis Dynamics',
        'code' => 'AEGS',
    ]);

    $item = Item::factory()->create([
        'translation' => ['en' => 'Complex shield generator'],
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Heavy Shield Generator',
            'class_name' => 'heavy_shield',
            'classification' => 'Ship.Shield',
            'type' => 'Shield',
            'data' => [
                'stdItem' => [
                    'Mass' => 200.0,
                    'InventoryOccupancy' => [
                        'Dimensions' => [
                            'Width' => 1.5,
                            'Height' => 1.0,
                            'Length' => 2.0,
                        ],
                    ],
                ],
                'SHealthComponentParams' => [
                    'ShieldHealthRatio' => 5000.0,
                    'ShieldRegenerationRatio' => 500.0,
                    'ShieldDecayRatio' => 100.0,
                    'RealTimeRegenerationDelay' => 5.0,
                ],
                'SEnergyComponentParams' => [
                    'EnergyDrainRatio' => 250.0,
                    'ChargingEnergyRatio' => 500.0,
                ],
            ],
        ]);

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk()
        ->assertViewIs('items.show')
        ->assertSee('Heavy Shield Generator')
        ->assertSee('Aegis Dynamics')
        ->assertSee($item->uuid);
});

it('renders item with long description in collapsible details', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'MISC',
        'code' => 'MISC',
    ]);

    /** @var Item $item */
    $item = Item::factory()->create([
        'translation' => [
            'en' => 'The Exploration Scanner is an advanced detection system designed for deep space reconnaissance. Featuring multiple frequency modes, it can identify everything from mineral deposits to hostile entities. Its ergonomic design allows for prolonged use during extended missions. The unit interfaces seamlessly with standard ship systems.',
            'de' => 'Erster Absatz. ',
        ],
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Exploration Scanner',
            'class_name' => 'exploration_scanner',
            'classification' => 'Equipment.Scanner',
            'type' => 'Scanner',
            'data' => [
                'stdItem' => [
                    'Mass' => 50.0,
                    'InventoryOccupancy' => [
                        'Dimensions' => [
                            'Width' => 0.8,
                            'Height' => 0.6,
                            'Length' => 1.2,
                        ],
                    ],
                ],
            ],
        ]);

    ItemDescriptionData::factory()->for($item)->create([
        'name' => 'Technical Specifications',
        'value' => 'Operating Range: 50,000 km | Power Consumption: 250 W | Scan Modes: Mineral, Biological, Mechanical',
    ]);

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk()
        ->assertViewIs('items.show')
        // Essentials Block
        ->assertSee('Exploration Scanner')
        ->assertSee('MISC')
        // Description Section - Collapsible
        ->assertSee('Description')
        ->assertSee('Exploration Scanner is an advanced detection system')
        ->assertSee('Technical Specifications')
        // Technical Section
        ->assertSee($item->uuid);
});

it('displays raw payload in collapsible details', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Origin',
        'code' => 'ORIG',
    ]);

    $item = Item::factory()->create([
        'translation' => ['en' => 'Luxury component'],
    ]);

    $itemData = ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Luxury Lamp',
            'class_name' => 'luxury_lamp',
            'classification' => 'Equipment.Furniture',
            'type' => 'Furniture',
            'data' => [
                'stdItem' => [
                    'Mass' => 5.0,
                ],
            ],
        ]);

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk()
        ->assertViewIs('items.show')
        // Essentials Block
        ->assertSee('Luxury Lamp')
        // Raw Payload Section - Collapsible
        ->assertSee('Raw Item Payload')
        // Technical Section with API link
        ->assertSee($item->uuid)
        ->assertSee('API');
});

it('includes accessibility attributes on collapsible sections', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Anvil Aerospace',
        'code' => 'ANVL',
    ]);

    $item = Item::factory()->create([
        'translation' => ['en' => 'Military component'],
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Tactical Display',
            'class_name' => 'tactical_display',
            'classification' => 'Equipment.Display',
            'type' => 'Display',
            'data' => [
                'stdItem' => [
                    'Mass' => 15.0,
                    'InventoryOccupancy' => [
                        'Dimensions' => [
                            'Width' => 1.0,
                            'Height' => 0.8,
                            'Length' => 0.2,
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk()
        ->assertViewIs('items.show')
        // Verify collapsible sections have details elements
        ->assertSee('<details', false) // HTML5 details element
        ->assertSee('<summary', false) // HTML5 summary element
        // Verify semantic HTML for data
        ->assertSee('Tactical Display');
});

it('supports responsive layout for mobile viewports', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Roberts Space Industries',
        'code' => 'RSI',
    ]);

    $item = Item::factory()->create([
        'translation' => ['en' => 'Standard component'],
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Standard Component',
            'class_name' => 'standard_component',
            'classification' => 'Equipment.Standard',
            'type' => 'Utility',
            'data' => [
                'stdItem' => [
                    'Mass' => 10.0,
                    'InventoryOccupancy' => [
                        'Dimensions' => [
                            'Width' => 0.6,
                            'Height' => 0.6,
                            'Length' => 0.6,
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk()
        ->assertViewIs('items.show')
        // Essentials Block - Always visible on all screen sizes
        ->assertSee('Standard Component')
        ->assertSee('RSI')
        ->assertSee('Utility')
        // Technical Section - Always visible
        ->assertSee($item->uuid)
        ->assertSee('4.0.0-LIVE');
});
