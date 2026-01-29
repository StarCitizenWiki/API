<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

it('shows an item by UUID', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Faction Jacket Green',
            'type' => 'Clothing',
            'class_name' => 'cds_armor_heavy_arms_01_02_01',
            'classification' => 'FPS.Clothing.Torso',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $item->uuid)
        ->assertJsonPath('data.name', 'Faction Jacket Green')
        ->assertJsonPath('data.class_name', 'cds_armor_heavy_arms_01_02_01');
});

it('shows an item by exact name', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Faction Jacket Green',
            'type' => 'Clothing',
            'class_name' => 'cds_armor_heavy_arms_01_02_01',
            'classification' => 'FPS.Clothing.Torso',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson('/api/items/Faction Jacket Green');

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $item->uuid)
        ->assertJsonPath('data.name', 'Faction Jacket Green');
});

it('shows an item by case-insensitive name', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Faction Jacket Green',
            'type' => 'Clothing',
            'class_name' => 'cds_armor_heavy_arms_01_02_01',
            'classification' => 'FPS.Clothing.Torso',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson('/api/items/faction jacket green');

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $item->uuid)
        ->assertJsonPath('data.name', 'Faction Jacket Green');
});

it('shows an item by exact class_name', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Heavy Armor Arms',
            'type' => 'Armor',
            'class_name' => 'cds_armor_heavy_arms_01_02_01',
            'classification' => 'FPS.Armor.Arms',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson('/api/items/cds_armor_heavy_arms_01_02_01');

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $item->uuid)
        ->assertJsonPath('data.name', 'Heavy Armor Arms')
        ->assertJsonPath('data.class_name', 'cds_armor_heavy_arms_01_02_01');
});

it('shows an item by case-insensitive class_name', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Heavy Armor Arms',
            'type' => 'Armor',
            'class_name' => 'cds_armor_heavy_arms_01_02_01',
            'classification' => 'FPS.Armor.Arms',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson('/api/items/CDS_ARMOR_HEAVY_ARMS_01_02_01');

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $item->uuid)
        ->assertJsonPath('data.name', 'Heavy Armor Arms')
        ->assertJsonPath('data.class_name', 'cds_armor_heavy_arms_01_02_01');
});

it('shows an item by class_name with spaces converted to underscores', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Heavy Armor Arms',
            'type' => 'Armor',
            'class_name' => 'cds_armor_heavy_arms_01_02_01',
            'classification' => 'FPS.Armor.Arms',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson('/api/items/cds armor heavy arms 01 02 01');

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $item->uuid)
        ->assertJsonPath('data.name', 'Heavy Armor Arms')
        ->assertJsonPath('data.class_name', 'cds_armor_heavy_arms_01_02_01');
});

it('shows an item by name with underscores converted from spaces', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Faction Jacket Green',
            'type' => 'Clothing',
            'class_name' => 'faction_jacket_green',
            'classification' => 'FPS.Clothing.Torso',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson('/api/items/Faction_Jacket_Green');

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $item->uuid)
        ->assertJsonPath('data.name', 'Faction Jacket Green');
});

it('returns not found for non-existent item', function () {
    $response = $this->getJson('/api/items/non-existent-item');

    $response->assertNotFound();
});

it('redirects to vehicle endpoint for vehicle items', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Vehicle',
            'type' => 'NOITEM_Vehicle',
            'class_name' => 'test_vehicle',
            'classification' => 'Vehicle',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertRedirect("/api/vehicles/{$item->uuid}");
});

it('includes related items when requested', function (): void {
    $baseItem = Item::factory()->create();
    $baseData = ItemData::factory()
        ->for($baseItem)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Base Item',
            'type' => 'Weapon',
            'class_name' => 'test_base',
            'classification' => 'WeaponPersonal',
            'data' => ['stdItem' => []],
        ]);

    $variantItem = Item::factory()->create();
    ItemData::factory()
        ->for($variantItem)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Variant Item',
            'type' => 'Weapon',
            'class_name' => 'test_variant',
            'classification' => 'WeaponPersonal',
            'base_id' => $baseData->id,
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson("/api/items/{$variantItem->uuid}?include=related_items");

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $variantItem->uuid)
        ->assertJsonStructure([
            'data' => [
                'related_items' => [
                    'set_name',
                    'base_item',
                    'variant_items',
                    'set_items',
                ],
            ],
        ]);
});

it('does not include related items when not requested', function (): void {
    $item = Item::factory()->create();
    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Item',
            'type' => 'Weapon',
            'class_name' => 'test_item',
            'classification' => 'WeaponPersonal',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonMissing(['related_items']);
});

it('does not include related items on index route even when requested', function (): void {
    $item = Item::factory()->create();
    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Item',
            'type' => 'Weapon',
            'class_name' => 'test_item',
            'classification' => 'WeaponPersonal',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson('/api/items?include=related_items');

    $response->assertSuccessful()
        ->assertJsonMissing(['related_items']);
});

it('includes web urls with version in item index', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Item',
            'type' => 'Widget',
            'class_name' => 'test_item',
            'classification' => 'Test',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson('/api/items?version=4.0.0-LIVE');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $item->uuid);

    expect($response->json('data.0.web_url'))->toContain('version=4.0.0-LIVE');
    expect($response->json('data.0.type_web_url'))->toContain('version=4.0.0-LIVE');
});

it('includes version in api link when version is requested in item show', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Item',
            'type' => 'Widget',
            'class_name' => 'test_item',
            'classification' => 'Test',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}?version=4.0.0-LIVE");

    $response->assertSuccessful();

    expect($response->json('data.link'))->toContain('version=4.0.0-LIVE');
});

it('does not include version in api link when version is not requested in item show', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Item',
            'type' => 'Widget',
            'class_name' => 'test_item',
            'classification' => 'Test',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful();

    expect($response->json('data.link'))->not->toContain('version=');
});
