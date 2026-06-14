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
use App\Models\Game\VariantGroup;
use App\Models\Game\VariantGroupItem;
use Illuminate\Testing\TestResponse;
use Symfony\Component\DomCrawler\Crawler;

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
        ->assertSeeText($uuid)
        ->assertSeeText('Classification')
        ->assertSeeText($classification)
        ->assertSeeText('Class Name')
        ->assertSeeText($className)
        ->assertSeeText('Version')
        ->assertSeeText($version);
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

function assertItemSeoCanonical(TestResponse $response, string $canonicalUrl): TestResponse
{
    $crawler = itemShowCrawler($response);
    $canonicalTag = $crawler->filter('link[rel="canonical"]');
    $ogUrlTag = $crawler->filter('meta[property="og:url"]');

    expect($canonicalTag->count())->toBe(1)
        ->and($canonicalTag->attr('href'))->toBe($canonicalUrl)
        ->and($ogUrlTag->count())->toBe(1)
        ->and($ogUrlTag->attr('content'))->toBe($canonicalUrl);

    return $response;
}

/**
 * @return array<int, array<string, mixed>>
 */
function itemStructuredData(TestResponse $response): array
{
    return itemShowCrawler($response)
        ->filter('script[type="application/ld+json"]')
        ->each(static fn (Crawler $node): array => json_decode($node->text(), true, 512, JSON_THROW_ON_ERROR));
}

/**
 * @return array<string, mixed>
 */
function itemStructuredDataBlock(TestResponse $response, string $type): array
{
    return collect(itemStructuredData($response))
        ->first(static fn (array $block): bool => data_get($block, '@type') === $type, []);
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
        ->assertSeeText('Explosive')
        ->assertSeeText($item->uuid)
        ->assertSeeText('4.0.0-LIVE');

    assertItemSeoMetadata($response, [
        'meta[name="description"]' => 'Base item description',
        'meta[name="keywords"]' => 'Test Module,PowerPlant,Acme Works,Test.Module,Size 2,Small,Star Citizen,SC',
        'meta[property="og:type"]' => 'website',
        'meta[property="og:title"]' => 'Test Module by Acme Works - PowerPlant Size 2 - Star Citizen',
        'meta[name="twitter:card"]' => 'summary',
        'meta[name="twitter:title"]' => 'Test Module by Acme Works - PowerPlant Size 2 - Star Citizen',
    ]);
    assertItemSeoCanonical($response, route('web.items.show', ['item' => $item->slug]));

    expect(trim(itemShowCrawler($response)->filter('title')->text()))
        ->toBe('Test Module by Acme Works - PowerPlant Size 2 - Star Citizen');

    $breadcrumbStructuredData = itemStructuredDataBlock($response, 'BreadcrumbList');
    $productStructuredData = itemStructuredDataBlock($response, 'Item');

    expect(itemStructuredData($response))->toHaveCount(2)
        ->and(data_get($breadcrumbStructuredData, 'itemListElement'))->toHaveCount(5)
        ->and(data_get($breadcrumbStructuredData, 'itemListElement.0.name'))->toBe('All Items')
        ->and(data_get($breadcrumbStructuredData, 'itemListElement.3.name'))->toBe('Power-Plants')
        ->and(data_get($breadcrumbStructuredData, 'itemListElement.4.item'))->toBe(route('web.items.show', ['item' => $item->slug]))
        ->and(data_get($productStructuredData, 'name'))->toBe('Test Module')
        ->and(data_get($productStructuredData, 'brand.name'))->toBe('Acme Works')
        ->and(data_get($productStructuredData, 'category'))->toBe('Power-Plants')
        ->and(data_get($productStructuredData, 'description'))->toBe('Base item description')
        ->and(data_get($productStructuredData, 'url'))->toBe(route('web.items.show', ['item' => $item->slug]));

    assertItemMetaPanels($response, showsPortsCard: true, portsCount: 1);
    assertTechnicalMetadataVisible($response, $item->uuid, 'Test.Module', 'test_module', '4.0.0-LIVE');
});

it('renders quoted item names in the page title without double-escaped entities', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Klaus & Werner',
        'code' => 'KLWE',
    ]);

    $item = Item::factory()->create([
        'translation' => ['en' => 'Calibrated "test" shot.'],
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Arrowhead "Pathfinder" Sniper Rifle',
            'class_name' => 'klwe_sniper_energy_01_imp01',
            'classification' => 'FPS.Weapon.Medium',
            'type' => 'WeaponPersonal',
            'sub_type' => 'Sniper',
            'size' => 4,
            'data' => [],
        ]);

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk();

    $title = trim(itemShowCrawler($response)->filter('title')->text());

    expect($title)
        ->toBe('Arrowhead "Pathfinder" Sniper Rifle by Klaus & Werner - WeaponPersonal FPS.Weapon.Medium - Star Citizen')
        ->and($title)->not->toContain('&quot;')
        ->and(itemShowCrawler($response)->filter('meta[name="description"]')->attr('content'))->toBe('Calibrated "test" shot.')
        ->and($response->getContent())->not->toContain('&amp;quot;');
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
        ->assertSeeText('Power')
        ->assertSeeText('Weapon');

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

    $variantGroup = VariantGroup::query()->create([
        'game_version_id' => $version->id,
        'set_name' => 'Laser Cannon',
    ]);

    VariantGroupItem::query()->create([
        'variant_group_id' => $variantGroup->id,
        'item_data_id' => $baseItemData->id,
        'variant_name' => 'Base',
        'sort_order' => 0,
        'is_base' => true,
    ]);

    for ($i = 1; $i <= 4; $i++) {
        $variantData = ItemData::query()
            ->where('name', "Laser Cannon Variant {$i}")
            ->first();

        VariantGroupItem::query()->create([
            'variant_group_id' => $variantGroup->id,
            'item_data_id' => $variantData->id,
            'variant_name' => "Variant {$i}",
            'sort_order' => $i,
            'is_base' => false,
        ]);
    }

    $response = $this->get(route('web.items.show', $baseItem->uuid));

    $response->assertOk()
        ->assertSeeText('Laser Cannon')
        ->assertSeeText('Behring')
        ->assertSeeText('Variant 1')
        ->assertSeeText('Variant 4');

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
        'value' => 'Operating Range: 50,000 km - Power Consumption: 250 W - Scan Modes: Mineral, Biological, Mechanical',
    ]);

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk()
        ->assertSeeText('Exploration Scanner')
        ->assertSeeText('MISC')
        ->assertSeeText('Exploration Scanner is an advanced detection system')
        ->assertSeeText('Technical Specifications')
        ->assertSeeText($item->uuid);
});

it('displays technical metadata in collapsible details', function (): void {
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
});

it('renders the item page with technical metadata', function (): void {
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
        ->assertSeeText('Roberts Space Industries')
        ->assertSeeText('Utility')
        ->assertSeeText($item->uuid)
        ->assertSeeText('4.0.0-LIVE');

    assertItemMetaPanels($response);
    assertTechnicalMetadataVisible($response, $item->uuid, 'Equipment.Standard', 'standard_component', '4.0.0-LIVE');
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
            'classification' => 'FPS.WeaponPersonal',
            'type' => 'WeaponPersonal',
            'sub_type' => 'Rifle',
            'data' => ['stdItem' => []],
        ]);

    $variantItem = Item::factory()->create([
        'translation' => ['en' => 'Variant rifle description'],
    ]);

    $variantItemData = ItemData::factory()
        ->for($variantItem)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Prototype Shadow Rifle',
            'class_name' => 'prototype_shadow_rifle',
            'classification' => 'FPS.WeaponPersonal',
            'type' => 'WeaponPersonal',
            'sub_type' => 'Rifle',
            'base_id' => $baseItemData->id,
            'is_craftable' => true,
            'data' => ['stdItem' => []],
        ]);

    $variantGroup = VariantGroup::query()->create([
        'game_version_id' => $version->id,
        'set_name' => 'Prototype Rifle',
    ]);

    VariantGroupItem::query()->create([
        'variant_group_id' => $variantGroup->id,
        'item_data_id' => $baseItemData->id,
        'variant_name' => 'Base',
        'sort_order' => 0,
        'is_base' => true,
    ]);

    VariantGroupItem::query()->create([
        'variant_group_id' => $variantGroup->id,
        'item_data_id' => $variantItemData->id,
        'variant_name' => 'Shadow',
        'sort_order' => 1,
        'is_base' => false,
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
        ->and($variantHero->filter('[data-testid="item-hero-pill-craftable"]')->attr('href'))->toBe(route('web.blueprints.show', ['blueprint' => $blueprint->uuid]))
        ->and($variantQuickFacts->count())->toBe(1)
        ->and($variantQuickFacts->filter('[data-testid="item-quick-facts-base-variant-link"]')->count())->toBe(1)
        ->and(trim($variantQuickFacts->filter('[data-testid="item-quick-facts-base-variant-link"]')->text()))->toBe('Prototype Base Rifle')
        ->and($variantQuickFacts->filter('[data-testid="item-quick-facts-base-variant-link"]')->attr('href'))->toBe(route('web.items.show', $baseItem->slug));

    $baseResponse = $this->get(route('web.items.show', $baseItem->uuid));

    $baseResponse->assertOk();

    $baseHero = itemHero($baseResponse);

    expect($baseHero->count())->toBe(1)
        ->and($baseHero->filter('[data-testid="item-hero-pill-variant-state"]')->count())->toBe(1)
        ->and(trim($baseHero->filter('[data-testid="item-hero-pill-variant-state"]')->text()))->toBe('Base Variant');
});

it('shows true dimensions alongside overridden dimensions in quick facts', function (): void {
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
        'translation' => ['en' => 'Item with UI dimension overrides'],
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Override Test Item',
            'class_name' => 'override_test_item',
            'classification' => 'Test.Module',
            'type' => 'PowerPlant',
            'sub_type' => 'Small',
            'size' => 1,
            'data' => [
                'stdItem' => [
                    'Mass' => 5.0,
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

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk();

    $quickFacts = itemQuickFacts($response);

    expect($quickFacts->count())->toBe(1)
        ->and($quickFacts->text())->toContain('3 × 1 × 2m')
        ->and($quickFacts->filter('span[title]')->count())->toBe(1)
        ->and($quickFacts->filter('span[title]')->attr('title'))->toBe('UI: 3.5 × 1.5 × 2.5m');
});

it('does not show true dimensions when no override exists', function (): void {
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
        'translation' => ['en' => 'Item without overrides'],
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'No Override Item',
            'class_name' => 'no_override_item',
            'classification' => 'Test.Module',
            'type' => 'PowerPlant',
            'sub_type' => 'Small',
            'size' => 1,
            'data' => [
                'stdItem' => [
                    'Mass' => 5.0,
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

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk();

    $quickFacts = itemQuickFacts($response);

    expect($quickFacts->count())->toBe(1)
        ->and($quickFacts->text())->toContain('3 × 1 × 2m')
        ->and($quickFacts->filter('span[title]')->count())->toBe(0);
});

it('shows cargo size row when cargo dimension is available', function (): void {
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
        'translation' => ['en' => 'Item with cargo dimensions'],
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Cargo Dim Item',
            'class_name' => 'cargo_dim_item',
            'classification' => 'Test.Module',
            'type' => 'PowerPlant',
            'sub_type' => 'Small',
            'size' => 1,
            'data' => [
                'stdItem' => [
                    'Mass' => 5.0,
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
                            'SCUConverted' => 19000,
                            'Unit' => 'µSCU',
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk();

    $quickFacts = itemQuickFacts($response);

    expect($quickFacts->count())->toBe(1)
        // True 3D dimensions shown as value
        ->and($quickFacts->text())->toContain('0.466 × 0.458 × 0.354m')
        // UI dimensions as title
        ->and($quickFacts->filter('span[title]')->count())->toBe(1)
        ->and($quickFacts->filter('span[title]')->attr('title'))->toBe('UI: 0.75 × 0.75 × 0.75m')
        // Cargo size shown as separate row
        ->and($quickFacts->text())->toContain('0.3 × 0.24 × 0.34m')
        ->and($quickFacts->text())->toContain('Cargo Size');
});

it('does not show cargo size row when no cargo dimension', function (): void {
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
        'translation' => ['en' => 'Item without cargo dims'],
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'No Cargo Dim Item',
            'class_name' => 'no_cargo_dim_item',
            'classification' => 'Test.Module',
            'type' => 'PowerPlant',
            'sub_type' => 'Small',
            'size' => 1,
            'data' => [
                'stdItem' => [
                    'Mass' => 5.0,
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

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk();

    $quickFacts = itemQuickFacts($response);

    expect($quickFacts->count())->toBe(1)
        ->and($quickFacts->text())->not->toContain('Cargo Size');
});

it('shows blueprint links in quick-facts card when item is craftable', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'CraftCorp',
        'code' => 'CRC',
    ]);

    $item = Item::factory()->create([
        'translation' => ['en' => 'Craftable Item'],
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Craftable Item',
            'class_name' => 'craftable_item',
            'classification' => 'WeaponPersonal',
            'type' => 'WeaponPersonal',
            'sub_type' => 'Rifle',
            'is_craftable' => true,
            'data' => ['stdItem' => []],
        ]);

    $blueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($version, 'gameVersion')
        ->create([
            'key' => 'BP_CRAFTABLE_ITEM',
            'output_item_uuid' => $item->uuid,
            'output_name' => 'Craftable Item Blueprint',
            'data' => [
                'output' => [
                    'uuid' => $item->uuid,
                    'name' => 'Craftable Item',
                    'class' => 'bp_craftable_item',
                ],
                'tiers' => [],
            ],
        ]);

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk();

    $quickFacts = itemQuickFacts($response);

    expect($quickFacts->count())->toBe(1)
        ->and($quickFacts->text())->toContain('Blueprints')
        ->and($quickFacts->text())->toContain('Craftable Item Blueprint');
});

it('does not show blueprints row in quick-facts card when item is not craftable', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'SimpleCorp',
        'code' => 'SMP',
    ]);

    $item = Item::factory()->create([
        'translation' => ['en' => 'Non Craftable Item'],
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Non Craftable Item',
            'class_name' => 'non_craftable_item',
            'classification' => 'WeaponPersonal',
            'type' => 'WeaponPersonal',
            'sub_type' => 'Rifle',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk();

    $quickFacts = itemQuickFacts($response);

    expect($quickFacts->count())->toBe(1)
        ->and($quickFacts->text())->not->toContain('Blueprints');
});

it('hides the related items card for cargo items', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'CargoCorp',
        'code' => 'CRCO',
    ]);

    $baseItem = Item::factory()->create([
        'translation' => ['en' => 'Base cargo description'],
    ]);

    $baseItemData = ItemData::factory()
        ->for($baseItem)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Standard Cargo Crate',
            'class_name' => 'standard_cargo_crate',
            'classification' => 'Cargo',
            'type' => 'Cargo',
            'data' => ['stdItem' => []],
        ]);

    $variantItem = Item::factory()->create([
        'translation' => ['en' => 'Variant cargo description'],
    ]);

    $variantItemData = ItemData::factory()
        ->for($variantItem)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Large Cargo Crate',
            'class_name' => 'large_cargo_crate',
            'classification' => 'Cargo',
            'type' => 'Cargo',
            'base_id' => $baseItemData->id,
            'data' => ['stdItem' => []],
        ]);

    $variantGroup = VariantGroup::query()->create([
        'game_version_id' => $version->id,
        'set_name' => 'Cargo Crate',
    ]);

    VariantGroupItem::query()->create([
        'variant_group_id' => $variantGroup->id,
        'item_data_id' => $baseItemData->id,
        'variant_name' => 'Base',
        'sort_order' => 0,
        'is_base' => true,
    ]);

    VariantGroupItem::query()->create([
        'variant_group_id' => $variantGroup->id,
        'item_data_id' => $variantItemData->id,
        'variant_name' => 'Large',
        'sort_order' => 1,
        'is_base' => false,
    ]);

    $response = $this->get(route('web.items.show', $baseItem->uuid));

    $response->assertOk();

    $relatedItemsCard = itemRelatedItemsCard($response);
    $quickFacts = itemQuickFacts($response);

    expect($relatedItemsCard->count())->toBe(0)
        ->and($quickFacts->text())->not->toContain('Related Items');
});

it('renders the hero image with layout-shift guards and lcp hints when an image is available', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $item = Item::factory()->create([
        'translation' => ['en' => 'Utility gadget description'],
    ]);

    $item->images = [[
        'source' => 'starcitizen.tools',
        'thumbnail_url' => 'https://cdn.test/utility-thumb.jpg',
        'thumbnail_width' => 600,
        'thumbnail_height' => 400,
        'original_url' => 'https://cdn.test/utility-full.jpg',
        'original_width' => 1200,
        'original_height' => 800,
    ]];
    $item->save();

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->create([
            'name' => 'Utility Gadget',
            'class_name' => 'utility_gadget',
            'classification' => 'Equipment.Standard',
            'type' => 'Utility',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk();

    $hero = itemHero($response);
    $img = $hero->filter('img')->first();

    expect($hero->count())->toBe(1)
        ->and($img->count())->toBe(1)
        ->and($img->attr('src'))->toBe('https://cdn.test/utility-thumb.jpg')
        ->and($img->attr('width'))->toBe('600')
        ->and($img->attr('height'))->toBe('400')
        ->and($img->attr('srcset'))->toBe('https://cdn.test/utility-thumb.jpg 600w, https://cdn.test/utility-full.jpg 1200w')
        ->and($img->attr('loading'))->toBe('eager')
        ->and($img->attr('fetchpriority'))->toBe('high')
        ->and($img->attr('class'))->toContain('object-cover')
        ->and($img->attr('class'))->toContain('max-h-96')
        ->and($hero->filter('figure span')->first()->text())->toContain('starcitizen.tools');
});

it('renders the hero icon chip with the daisyui rounded-box radius when no image is available', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $item = Item::factory()->create([
        'translation' => ['en' => 'Power plant description'],
    ]);

    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->create([
            'name' => 'Turbo Power Plant',
            'class_name' => 'turbo_power_plant',
            'classification' => 'Ship.PowerPlant',
            'type' => 'PowerPlant',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->get(route('web.items.show', $item->uuid));

    $response->assertOk();

    $iconChip = itemHero($response)->filter('span[aria-label="Item type"]')->first();

    expect($iconChip->count())->toBe(1)
        ->and($iconChip->attr('class'))->toContain('rounded-box')
        ->and($iconChip->attr('class'))->not->toContain('rounded-xl');
});
