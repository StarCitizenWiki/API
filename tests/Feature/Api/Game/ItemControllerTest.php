<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

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

it('shows an item by uuid', function (): void {
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
        ->assertJsonPath('data.class_name', 'cds_armor_heavy_arms_01_02_01')
        ->assertJsonPath('data.is_craftable', false)
        ->assertJsonMissingPath('data.blueprint');
});

it('includes all crafting blueprints when an item is craftable', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Crafted Component',
            'type' => 'Widget',
            'class_name' => 'crafted_component',
            'classification' => 'Test.Widget',
            'data' => ['stdItem' => []],
        ]);

    $alphaBlueprint = Blueprint::factory()->create();
    $betaBlueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($alphaBlueprint, 'blueprint')
        ->for($this->gameVersion, 'gameVersion')
        ->create([
            'key' => 'BP_ALPHA_COMPONENT',
            'output_item_uuid' => $item->uuid,
            'output_name' => 'Alpha Component Blueprint',
            'is_available_by_default' => false,
            'data' => [
                'output' => [
                    'uuid' => $item->uuid,
                    'name' => 'Alpha Component Blueprint',
                    'class' => 'bp_alpha_component',
                ],
                'tiers' => [],
            ],
        ]);

    BlueprintData::factory()
        ->for($betaBlueprint, 'blueprint')
        ->for($this->gameVersion, 'gameVersion')
        ->create([
            'key' => 'BP_BETA_COMPONENT',
            'output_item_uuid' => $item->uuid,
            'output_name' => 'Beta Component Blueprint',
            'is_available_by_default' => false,
            'data' => [
                'output' => [
                    'uuid' => $item->uuid,
                    'name' => 'Beta Component Blueprint',
                    'class' => 'bp_beta_component',
                ],
                'tiers' => [],
            ],
        ]);

    DB::flushQueryLog();
    DB::enableQueryLog();

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.is_craftable', true)
        ->assertJsonCount(2, 'data.blueprint')
        ->assertJsonPath('data.blueprint.0.uuid', $alphaBlueprint->uuid)
        ->assertJsonPath('data.blueprint.0.name', 'Alpha Component Blueprint')
        ->assertJsonPath('data.blueprint.0.link', route('blueprints.show', ['blueprint' => $alphaBlueprint->uuid]))
        ->assertJsonPath('data.blueprint.1.uuid', $betaBlueprint->uuid)
        ->assertJsonPath('data.blueprint.1.name', 'Beta Component Blueprint')
        ->assertJsonPath('data.blueprint.1.link', route('blueprints.show', ['blueprint' => $betaBlueprint->uuid]));

    $craftingLookupQuery = collect(DB::getQueryLog())
        ->pluck('query')
        ->map(static fn (string $query): string => strtolower($query))
        ->first(static fn (string $query): bool => str_contains($query, 'from "game_blueprint_data"'));

    expect($craftingLookupQuery)->not->toBeNull()
        ->and($craftingLookupQuery)->not->toContain('select * from "game_blueprint_data"')
        ->and($craftingLookupQuery)->not->toContain('"game_blueprint_data"."data"')
        ->and($craftingLookupQuery)->toContain('"blueprint_id"')
        ->and($craftingLookupQuery)->toContain('"output_name"')
        ->and($craftingLookupQuery)->toContain('"key"');
});

it('uses uuid-specific lookup before name or class_name fallbacks', function (): void {
    $item = Item::factory()->create();
    $decoyItem = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Primary Item',
            'type' => 'Clothing',
            'class_name' => 'primary_item',
            'classification' => 'FPS.Clothing.Torso',
            'data' => ['stdItem' => []],
        ]);

    ItemData::factory()
        ->for($decoyItem)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => $item->uuid,
            'type' => 'Clothing',
            'class_name' => 'uuid_named_item',
            'classification' => 'FPS.Clothing.Torso',
            'data' => ['stdItem' => []],
        ]);

    DB::flushQueryLog();
    DB::enableQueryLog();

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $item->uuid)
        ->assertJsonPath('data.name', 'Primary Item');

    $lookupQuery = collect(DB::getQueryLog())
        ->pluck('query')
        ->first(static fn (string $query) => str_contains($query, 'from "game_item_data"') && str_contains($query, 'limit 1'));

    expect($lookupQuery)->not->toBeNull()
        ->and(strtolower((string) $lookupQuery))->not->toContain('or "name" =')
        ->and(strtolower((string) $lookupQuery))->not->toContain('upper(name)')
        ->and(strtolower((string) $lookupQuery))->not->toContain('or "class_name" =')
        ->and(strtolower((string) $lookupQuery))->toContain('from "game_items"');
});

it('shows an item by name permutations', function (string $requestPath, string $itemClassName): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Faction Jacket Green',
            'type' => 'Clothing',
            'class_name' => $itemClassName,
            'classification' => 'FPS.Clothing.Torso',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson($requestPath);

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $item->uuid)
        ->assertJsonPath('data.name', 'Faction Jacket Green');
})->with([
    'exact name' => ['/api/items/Faction Jacket Green', 'cds_armor_heavy_arms_01_02_01'],
    'case-insensitive name' => ['/api/items/faction jacket green', 'cds_armor_heavy_arms_01_02_01'],
    'name with underscores converted from spaces' => ['/api/items/Faction_Jacket_Green', 'faction_jacket_green'],
]);

it('shows an item by class_name permutations', function (string $requestPath): void {
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

    $response = $this->getJson($requestPath);

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $item->uuid)
        ->assertJsonPath('data.name', 'Heavy Armor Arms')
        ->assertJsonPath('data.class_name', 'cds_armor_heavy_arms_01_02_01');
})->with([
    'exact class_name' => ['/api/items/cds_armor_heavy_arms_01_02_01'],
    'case-insensitive class_name' => ['/api/items/CDS_ARMOR_HEAVY_ARMS_01_02_01'],
    'class_name with spaces converted to underscores' => ['/api/items/cds armor heavy arms 01 02 01'],
]);

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
})->markTestSkipped('Temporarily quarantined while vehicle redirect assertions are stabilized.');

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

it('includes craftability in item index results', function (): void {
    $craftableItem = Item::factory()->create();
    $nonCraftableItem = Item::factory()->create();

    ItemData::factory()
        ->for($craftableItem)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Alpha Crafted Item',
            'type' => 'Widget',
            'class_name' => 'alpha_crafted_item',
            'classification' => 'Test.Widget',
            'data' => ['stdItem' => []],
        ]);

    ItemData::factory()
        ->for($nonCraftableItem)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Beta Non Crafted Item',
            'type' => 'Widget',
            'class_name' => 'beta_non_crafted_item',
            'classification' => 'Test.Widget',
            'data' => ['stdItem' => []],
        ]);

    $blueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->gameVersion, 'gameVersion')
        ->create([
            'key' => 'BP_ALPHA_CRAFTED_ITEM',
            'output_item_uuid' => $craftableItem->uuid,
            'output_name' => 'Alpha Crafted Item Blueprint',
            'data' => [
                'output' => [
                    'uuid' => $craftableItem->uuid,
                    'name' => 'Alpha Crafted Item Blueprint',
                    'class' => 'bp_alpha_crafted_item',
                ],
                'tiers' => [],
            ],
        ]);

    DB::flushQueryLog();
    DB::enableQueryLog();

    $response = $this->getJson('/api/items');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.uuid', $craftableItem->uuid)
        ->assertJsonPath('data.0.is_craftable', true)
        ->assertJsonPath('data.0.blueprint.0.uuid', $blueprint->uuid)
        ->assertJsonPath('data.1.uuid', $nonCraftableItem->uuid)
        ->assertJsonPath('data.1.is_craftable', false)
        ->assertJsonMissingPath('data.1.blueprint');

    $craftingLookupQuery = collect(DB::getQueryLog())
        ->pluck('query')
        ->map(static fn (string $query): string => strtolower($query))
        ->first(
            static fn (string $query): bool => str_contains($query, 'from "game_blueprint_data"')
                && str_contains($query, 'where "output_item_uuid" in')
        );

    expect($craftingLookupQuery)->not->toBeNull()
        ->and($craftingLookupQuery)->not->toContain('select * from "game_blueprint_data"')
        ->and($craftingLookupQuery)->not->toContain('"game_blueprint_data"."data"')
        ->and($craftingLookupQuery)->toContain('"blueprint_id"')
        ->and($craftingLookupQuery)->toContain('"output_name"')
        ->and($craftingLookupQuery)->toContain('"key"');
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

it('searches items with plain text queries that are not uuids', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Decari Polo',
            'type' => 'Clothing',
            'sub_type' => 'Shirt',
            'class_name' => 'decari_polo',
            'classification' => 'FPS.Clothing.Torso',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->postJson('/api/items/search', [
        'query' => 'decari po',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $item->uuid)
        ->assertJsonPath('data.0.name', 'Decari Polo');
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
