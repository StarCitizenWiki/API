<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\EntityTag;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\ItemDescriptionData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Symfony\Component\DomCrawler\Crawler;

uses(RefreshDatabase::class);

function itemShowCrawler(TestResponse $response): Crawler
{
    return new Crawler($response->getContent());
}

function itemDetailsPanel(TestResponse $response, string $summary): Crawler
{
    return itemShowCrawler($response)->filterXPath(sprintf(
        '//details[.//summary[contains(normalize-space(.), "%s")]]',
        $summary,
    ));
}

function itemHero(TestResponse $response): Crawler
{
    return itemShowCrawler($response)->filter('[data-testid="item-hero"]');
}

function itemQuickFacts(TestResponse $response): Crawler
{
    return itemShowCrawler($response)->filter('[data-testid="item-quick-facts-card"]');
}

function itemRelatedItemsCard(TestResponse $response): Crawler
{
    return itemShowCrawler($response)->filter('[data-testid="item-related-items-card"]');
}

function assertItemMetaPanels(TestResponse $response, bool $showsPortsCard = false, ?int $portsCount = null): TestResponse
{
    expect(itemDetailsPanel($response, 'Technical')->count())->toBe(1);
    expect(itemDetailsPanel($response, 'Raw Item Payload')->count())->toBe(1);

    if ($showsPortsCard) {
        $portsPanel = itemShowCrawler($response)->filter('[data-testid="item-ports-card"]');

        expect($portsPanel->count())->toBe(1);

        if ($portsCount !== null) {
            expect($portsPanel->text())->toContain((string) $portsCount);
        }

        return $response;
    }

    expect(itemShowCrawler($response)->filter('[data-testid="item-ports-card"]')->count())->toBe(0);

    return $response;
}

function assertTechnicalMetadataVisible(
    TestResponse $response,
    string $uuid,
    string $classification,
    string $className,
    string $version,
): TestResponse {
    return $response->assertSeeText('Technical')
        ->assertSeeText('UUID')
        ->assertSeeText($uuid)
        ->assertSeeText('Classification')
        ->assertSeeText($classification)
        ->assertSeeText('Class Name')
        ->assertSeeText($className)
        ->assertSeeText('Game Version')
        ->assertSeeText($version)
        ->assertSeeText('API Link')
        ->assertSeeText('Entity Tag Map')
        ->assertSee(route('items.show', ['identifier' => $uuid]), false);
}

function assertRawPayloadVisible(TestResponse $response, array $snippets): TestResponse
{
    $response->assertSeeText('Raw Item Payload');

    foreach ($snippets as $snippet) {
        $response->assertSeeText($snippet);
    }

    return $response;
}

function assertItemSeoMetadata(TestResponse $response, array $metadata): TestResponse
{
    $crawler = itemShowCrawler($response);

    foreach ($metadata as $selector => $content) {
        $tag = $crawler->filter($selector);

        expect($tag->count())->toBe(1)
            ->and($tag->attr('content'))->toBe($content);
    }

    return $response;
}

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
        ->assertSeeText('Test.Module')
        ->assertSeeText('Test Module')
        ->assertSeeText('Acme Works')
        ->assertSeeText('PowerPlant')
        ->assertSeeText('Main Port')
        ->assertSeeText('Test Module Variant')
        ->assertSeeText('Explosive')
        ->assertSeeText($item->uuid)
        ->assertSeeText('4.0.0-LIVE');

    $searchForm = itemShowCrawler($response)->filter(sprintf('form[action="%s"]', route('web.items.index')));

    expect($searchForm->count())->toBe(1)
        ->and($searchForm->filter('input[name="filter[name]"]')->count())->toBe(1)
        ->and($searchForm->filter('button[type="submit"]')->count())->toBe(1);

    assertItemSeoMetadata($response, [
        'meta[name="keywords"]' => 'Test Module,PowerPlant,Acme Works,Test.Module,Star Citizen,SC',
        'meta[property="og:type"]' => 'website',
        'meta[property="og:title"]' => 'Test Module - PowerPlant Acme Works',
        'meta[name="twitter:card"]' => 'summary',
        'meta[name="twitter:title"]' => 'Test Module - PowerPlant',
    ]);

    assertItemMetaPanels($response, showsPortsCard: true, portsCount: 1);
    assertTechnicalMetadataVisible($response, $item->uuid, 'Test.Module', 'test_module', '4.0.0-LIVE');
    assertRawPayloadVisible($response, [
        '"name": "Test Module"',
        '"class_name": "test_module"',
        '"classification": "Test.Module"',
        '"type": "PowerPlant"',
    ]);
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
        ->assertSeeText('Minimal Module')
        ->assertSeeText('minimal_module')
        ->assertSeeText('Acme Works')
        ->assertSeeText('PowerPlant')
        ->assertSeeText('Small')
        ->assertSeeText($item->uuid)
        ->assertSeeText('4.0.0-LIVE');

    assertItemMetaPanels($response);
    assertTechnicalMetadataVisible($response, $item->uuid, 'Test.Module', 'minimal_module', '4.0.0-LIVE');
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
        ->assertSeeText('Ship Core')
        ->assertSeeText('Drake Interplanetary')
        ->assertSeeText('Power Port 1')
        ->assertSeeText('Weapon Port Left');

    assertItemMetaPanels($response, showsPortsCard: true, portsCount: 6);
    assertTechnicalMetadataVisible($response, $item->uuid, 'Ship.Component', 'ship_core', '4.0.0-LIVE');
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
        ->assertSeeText('Laser Cannon')
        ->assertSeeText('Behring')
        ->assertSeeText('Laser Cannon Variant 1')
        ->assertSeeText('Laser Cannon Variant 4');

    $relatedItemsCard = itemRelatedItemsCard($response);

    expect($relatedItemsCard->count())->toBe(1)
        ->and(itemShowCrawler($response)->filter('[data-testid="item-description-data-card"]')->count())->toBe(0)
        ->and($relatedItemsCard->attr('class'))->not->toContain('xl:col-span-2');
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
        ->assertSeeText('Heavy Shield Generator')
        ->assertSeeText('Aegis Dynamics')
        ->assertSeeText($item->uuid);
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
        ->assertSeeText('Exploration Scanner')
        ->assertSeeText('MISC')
        ->assertSeeText('Exploration Scanner is an advanced detection system')
        ->assertSeeText('Technical Specifications')
        ->assertSeeText($item->uuid);
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
        ->assertSeeText('Luxury Lamp')
        ->assertSeeText($item->uuid);

    assertItemMetaPanels($response);
    assertTechnicalMetadataVisible($response, $item->uuid, 'Equipment.Furniture', 'luxury_lamp', '4.0.0-LIVE');
    assertRawPayloadVisible($response, [
        '"name": "Luxury Lamp"',
        '"class_name": "luxury_lamp"',
        '"classification": "Equipment.Furniture"',
        '"type": "Furniture"',
    ]);
});

it('renders the item page with technical metadata and raw payload details', function (): void {
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
        ->assertSeeText('Tactical Display')
        ->assertSeeText('Anvil Aerospace');

    assertItemMetaPanels($response);
    assertTechnicalMetadataVisible($response, $item->uuid, 'Equipment.Display', 'tactical_display', '4.0.0-LIVE');
    assertRawPayloadVisible($response, [
        '"name": "Tactical Display"',
        '"class_name": "tactical_display"',
        '"classification": "Equipment.Display"',
        '"type": "Display"',
    ]);
});

it('renders the item page with core metadata', function (): void {
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
        ->assertSeeText('Standard Component')
        ->assertSeeText('RSI')
        ->assertSeeText('Utility')
        ->assertSeeText($item->uuid)
        ->assertSeeText('4.0.0-LIVE');

    assertItemMetaPanels($response);
    assertTechnicalMetadataVisible($response, $item->uuid, 'Equipment.Standard', 'standard_component', '4.0.0-LIVE');
    assertRawPayloadVisible($response, [
        '"name": "Standard Component"',
        '"class_name": "standard_component"',
        '"classification": "Equipment.Standard"',
        '"type": "Utility"',
    ]);
});

it('shows variant state in the hero and base variant link in quick facts', function (): void {
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
        'translation' => ['en' => 'Base rifle description'],
    ]);

    $baseItemData = ItemData::factory()
        ->for($baseItem)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Prototype Base Rifle',
            'class_name' => 'prototype_base_rifle',
            'classification' => 'WeaponPersonal',
            'type' => 'WeaponPersonal',
            'sub_type' => 'Rifle',
            'data' => ['stdItem' => []],
        ]);

    $variantItem = Item::factory()->create([
        'translation' => ['en' => 'Variant rifle description'],
    ]);

    ItemData::factory()
        ->for($variantItem)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Prototype Shadow Rifle',
            'class_name' => 'prototype_shadow_rifle',
            'classification' => 'WeaponPersonal',
            'type' => 'WeaponPersonal',
            'sub_type' => 'Rifle',
            'base_id' => $baseItemData->id,
            'data' => ['stdItem' => []],
        ]);

    $blueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($version, 'gameVersion')
        ->create([
            'key' => 'BP_PROTOTYPE_SHADOW_RIFLE',
            'output_item_uuid' => $variantItem->uuid,
            'output_name' => 'Prototype Shadow Rifle Blueprint',
            'data' => [
                'output' => [
                    'uuid' => $variantItem->uuid,
                    'name' => 'Prototype Shadow Rifle',
                    'class' => 'bp_prototype_shadow_rifle',
                ],
                'tiers' => [],
            ],
        ]);

    $variantResponse = $this->get(route('web.items.show', $variantItem->uuid));

    $variantResponse->assertOk();

    $variantHero = itemHero($variantResponse);
    $variantQuickFacts = itemQuickFacts($variantResponse);

    expect($variantHero->count())->toBe(1)
        ->and($variantHero->filter('[data-testid="item-hero-pill-variant-state"]')->count())->toBe(1)
        ->and(trim($variantHero->filter('[data-testid="item-hero-pill-variant-state"]')->text()))->toBe('Variant')
        ->and($variantHero->filter('[data-testid="item-hero-pill-base-variant"]')->count())->toBe(0)
        ->and($variantHero->filter('[data-testid="item-hero-pill-craftable"]')->count())->toBe(1)
        ->and($variantHero->filter('[data-testid="item-hero-pill-craftable"]')->attr('href'))->toBe(route('web.blueprints.show', ['blueprint' => $blueprint->uuid]));

    expect($variantQuickFacts->count())->toBe(1)
        ->and($variantQuickFacts->filter('[data-testid="item-quick-facts-base-variant-link"]')->count())->toBe(1)
        ->and(trim($variantQuickFacts->filter('[data-testid="item-quick-facts-base-variant-link"]')->text()))->toBe('Prototype Base Rifle')
        ->and($variantQuickFacts->filter('[data-testid="item-quick-facts-base-variant-link"]')->attr('href'))->toBe(route('web.items.show', $baseItem->uuid));

    $baseResponse = $this->get(route('web.items.show', $baseItem->uuid));

    $baseResponse->assertOk();

    $baseHero = itemHero($baseResponse);

    expect($baseHero->count())->toBe(1)
        ->and($baseHero->filter('[data-testid="item-hero-pill-variant-state"]')->count())->toBe(1)
        ->and(trim($baseHero->filter('[data-testid="item-hero-pill-variant-state"]')->text()))->toBe('Base Variant');
});
