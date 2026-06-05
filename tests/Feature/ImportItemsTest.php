<?php

declare(strict_types=1);

use App\Jobs\Game\ImportItemData;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\EntityTag;
use App\Models\Game\GameLabel;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\ItemDescriptionData;
use App\Models\Game\Manufacturer;
use App\Models\System\Language;
use App\Services\Parser\SC\Labels;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Labels::flushCache();
    Cache::flush();
});

it('fails when the game version does not exist', function (): void {
    $this->artisan('game:import-items', ['version' => 'missing'])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('Game version "missing" does not exist. Please create it first.');
});

it('dispatches an import job for each item file', function (): void {
    Storage::fake('scunpacked');
    Queue::fake();

    $version = GameVersion::query()->create([
        'code' => '3.23.0',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    Storage::disk('scunpacked')->put('items/alpha.json', json_encode(['Item' => ['reference' => 'alpha']], JSON_THROW_ON_ERROR));
    Storage::disk('scunpacked')->put('items/beta.json', json_encode(['Item' => ['reference' => 'beta']], JSON_THROW_ON_ERROR));
    Storage::disk('scunpacked')->put('items/ignore.txt', 'not json');

    $this->artisan('game:import-items', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Dispatched 2 item import jobs for version 3.23.0.');

    Queue::assertPushed(ImportItemData::class, 2);
});

it('imports item data, description data, and translations and upserts on re-run', function (): void {
    Storage::fake('scunpacked');

    GameLabel::factory()->asItemDescTest()->create();
    $labels = new Labels;

    $version = GameVersion::query()->create([
        'code' => '3.23.1',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $manufacturerUuid = fake()->uuid();
    $manufacturer = Manufacturer::query()->create([
        'uuid' => $manufacturerUuid,
        'name' => 'Roberts Space Industries',
        'code' => 'RSI',
    ]);

    $itemUuid = fake()->uuid();
    $payload = [
        'Item' => [
            'reference' => $itemUuid,
            'className' => 'RSI_Test_Item',
            'itemName' => 'Test Item Name',
            'name' => 'Test Item',
            'type' => 'Weapon',
            'subType' => 'Gun',
            'manufacturer' => 'RSI',
            'size' => 2,
            'grade' => 3,
            'mass' => 99,
            'tags' => ['tag-a'],
            'stdItem' => [
                'Description' => 'English description',
                'DescriptionText' => 'English description',
                'DescriptionData' => [
                    'Item Type' => 'Weapon',
                    'Damage' => '10',
                ],
                'Manufacturer' => [
                    'Code' => 'RSI',
                    'UUID' => $manufacturerUuid,
                ],
            ],
        ],
        'Raw' => [
            'Entity' => [
                'Components' => [
                    'SAttachableComponentParams' => [
                        'AttachDef' => [
                            'Localization__Description' => '@item_Desc_test',
                            'Localization' => [
                                '__Description' => '@item_Desc_test',
                                'English' => [
                                    'Description' => 'English description',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    Storage::disk('scunpacked')->put('items/test.json', json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportItemData($version->id, 'items/test.json', $labels))->handle();

    $item = Item::query()->firstWhere('uuid', $itemUuid);
    expect($item)->not->toBeNull();

    $data = ItemData::query()
        ->where('item_id', $item->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($data)->not->toBeNull()
        ->and($data->manufacturer_id)->toBe($manufacturer->id)
        ->and($data->name)->toBe('Test Item')
        ->and($data->data)->toHaveKey('tags')
        ->and($data->data)->not->toHaveKey('name');

    $descriptionData = ItemDescriptionData::query()
        ->where('item_id', $item->id)
        ->orderBy('name')
        ->get();

    expect($descriptionData)->toHaveCount(2)
        ->and($descriptionData->first()->name)->toBe('Damage')
        ->and($descriptionData->first()->value)->toBe('10')
        ->and($item->getTranslation('translation', Language::ENGLISH, false))->toBe('English description')
        ->and($item->getTranslation('translation', Language::CHINESE, false))->toBe('中文描述')
        ->and($item->getTranslation('translation', Language::GERMAN, false))->toBe('Deutsche Beschreibung')
        ->and($item->getTranslation('translation', Language::FRENCH, false))->toBeEmpty();

    // Re-run with updated payload to verify upsert behaviour
    $payload['Item']['grade'] = 4;
    $payload['Item']['stdItem']['DescriptionText'] = 'Updated English';
    $payload['Item']['stdItem']['Description'] = 'Updated English';
    $payload['Raw']['Entity']['Components']['SAttachableComponentParams']['AttachDef']['Localization']['English']['Description'] = 'Updated English';

    Storage::disk('scunpacked')->put('items/test.json', json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportItemData($version->id, 'items/test.json', $labels))->handle();

    $data->refresh();
    expect($data->grade)->toBe(4);

    $item->refresh();
    expect($item->getTranslation('translation', Language::ENGLISH, false))->toBe('Updated English');
});

it('skips chinese translation when the key is missing and uses stditem manufacturer fallback', function (): void {
    Storage::fake('scunpacked');

    // Test with no labels in database to test missing key lookup
    $labels = new Labels;

    $version = GameVersion::query()->create([
        'code' => '3.23.2',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $manufacturerUuid = fake()->uuid();
    $manufacturer = Manufacturer::query()->create([
        'uuid' => $manufacturerUuid,
        'name' => 'Unknown Manufacturer',
        'code' => 'UNKN',
    ]);

    $itemUuid = fake()->uuid();
    $payload = [
        'Item' => [
            'reference' => $itemUuid,
            'className' => 'Unknown_Test_Item',
            'itemName' => 'Unknown Item Name',
            'type' => 'Misc',
            'subType' => 'UNDEFINED',
            'manufacturer' => null,
            'size' => 1,
            'grade' => 1,
            'stdItem' => [
                'Manufacturer' => [
                    'Code' => 'UNKN',
                    'UUID' => $manufacturerUuid,
                ],
                'Description' => 'Fallback description',
            ],
        ],
        'Raw' => [
            'Entity' => [
                'Components' => [
                    'SAttachableComponentParams' => [
                        'AttachDef' => [
                            'Localization__Description' => '@key_does_not_exist_in_fixtures',
                            'Localization' => [
                                '__Description' => '@key_does_not_exist_in_fixtures',
                                'English' => [
                                    'Description' => 'Fallback description',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    Storage::disk('scunpacked')->put('items/unknown.json', json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportItemData($version->id, 'items/unknown.json', $labels))->handle();

    $item = Item::query()->firstWhere('uuid', $itemUuid);
    expect($item)->not->toBeNull();

    $data = ItemData::query()
        ->where('item_id', $item->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($data)->not->toBeNull()
        ->and($data->manufacturer_id)->toBe($manufacturer->id)
        ->and($item->getTranslation('translation', Language::CHINESE, false))->toBeEmpty()
        ->and($item->getTranslation('translation', Language::ENGLISH, false))->not->toBeNull();

});

it('imports and syncs entity tags and removes outdated tags on re-run', function (): void {
    Storage::fake('scunpacked');

    GameLabel::factory()->asItemDescTest()->create();
    $labels = new Labels;

    $manufacturerUuid = fake()->uuid();
    $manufacturer = Manufacturer::query()->create([
        'uuid' => $manufacturerUuid,
        'name' => 'Unknown Manufacturer',
        'code' => 'UNKN',
    ]);

    $version = GameVersion::query()->create([
        'code' => '3.24.0',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $itemUuid = fake()->uuid();
    $payload = [
        'Item' => [
            'reference' => $itemUuid,
            'className' => 'TST_Tagged_Item',
            'itemName' => 'Tagged Item',
            'type' => 'Clothing',
            'stdItem' => [
                'Manufacturer' => [
                    'Code' => 'TST',
                    'UUID' => $manufacturerUuid,
                ],
            ],
            'entity_tag_map' => [
                [
                    'tag' => '65124877-3571-4f63-b4a5-650a79e5bfb6',
                    'name' => 'Fashionable',
                ],
                [
                    'tag' => 'bf3c7235-1d79-468e-b981-2494f00b8f18',
                    'name' => 'EveryDay',
                ],
            ],
        ],
        'Raw' => [],
    ];

    Storage::disk('scunpacked')->put('items/tagged.json', json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportItemData($version->id, 'items/tagged.json', $labels))->handle();

    $item = Item::query()->firstWhere('uuid', $itemUuid);
    expect($item)->not->toBeNull();

    $data = ItemData::query()
        ->where('item_id', $item->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($data)->not->toBeNull();

    $entityTags = $data->entityTags;
    expect($entityTags)->toHaveCount(2)
        ->and($entityTags->pluck('name')->sort()->values()->all())->toBe(['EveryDay', 'Fashionable'])
        ->and($entityTags->pluck('uuid')->all())->toContain('65124877-3571-4f63-b4a5-650a79e5bfb6');

    // Verify tags are normalized (shared across items)
    $fashionableTag = EntityTag::query()->where('name', 'Fashionable')->first();
    expect($fashionableTag)->not->toBeNull()
        ->and($fashionableTag->uuid)->toBe('65124877-3571-4f63-b4a5-650a79e5bfb6');

    // Re-run with updated tags to verify sync behavior
    $payload['Item']['entity_tag_map'] = [
        [
            'tag' => '65124877-3571-4f63-b4a5-650a79e5bfb6',
            'name' => 'Fashionable',
        ],
        [
            'tag' => fake()->uuid(),
            'name' => 'NewTag',
        ],
    ];

    Storage::disk('scunpacked')->put('items/tagged.json', json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportItemData($version->id, 'items/tagged.json', $labels))->handle();

    $data->refresh();
    $entityTags = $data->entityTags;
    expect($entityTags)->toHaveCount(2)
        ->and($entityTags->pluck('name')->sort()->values()->all())->toBe(['Fashionable', 'NewTag'])
        ->and($entityTags->pluck('name')->all())->not->toContain('EveryDay');
});

it('handles items with no entity tags', function (): void {
    Storage::fake('scunpacked');

    GameLabel::factory()->asItemDescTest()->create();
    $labels = new Labels;

    $manufacturerUuid = fake()->uuid();
    $manufacturer = Manufacturer::query()->create([
        'uuid' => $manufacturerUuid,
        'name' => 'Unknown Manufacturer',
        'code' => 'UNKN',
    ]);

    $version = GameVersion::query()->create([
        'code' => '3.24.1',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $itemUuid = fake()->uuid();

    $payload = [
        'Item' => [
            'reference' => $itemUuid,
            'className' => 'TST_No_Tags',
            'itemName' => 'Item Without Tags',
            'type' => 'Misc',
            'stdItem' => [
                'Manufacturer' => [
                    'Code' => 'TS2',
                    'UUID' => $manufacturerUuid,
                ],
            ],
        ],
        'Raw' => [],
    ];

    Storage::disk('scunpacked')->put('items/notags.json', json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportItemData($version->id, 'items/notags.json', $labels))->handle();

    $item = Item::query()->firstWhere('uuid', $itemUuid);
    expect($item)->not->toBeNull();

    $data = ItemData::query()
        ->where('item_id', $item->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($data)->not->toBeNull()
        ->and($data->entityTags)->toHaveCount(0);
});

it('reuses normalized entity tags across item imports', function (): void {
    Storage::fake('scunpacked');

    GameLabel::factory()->asItemDescTest()->create();
    $labels = new Labels;

    $manufacturerUuid = fake()->uuid();
    $manufacturer = Manufacturer::query()->create([
        'uuid' => $manufacturerUuid,
        'name' => 'Unknown Manufacturer',
        'code' => 'UNKN',
    ]);

    $version = GameVersion::query()->create([
        'code' => '3.25.0',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $sharedTags = [
        [
            'tag' => fake()->uuid(),
            'name' => 'Existing One',
        ],
        [
            'tag' => fake()->uuid(),
            'name' => 'Existing Two',
        ],
        [
            'tag' => fake()->uuid(),
            'name' => 'New One',
        ],
    ];

    $firstItemUuid = fake()->uuid();
    $firstPayload = [
        'Item' => [
            'reference' => $firstItemUuid,
            'className' => 'OPT_First_Item',
            'itemName' => 'Optimized Item One',
            'type' => 'Test',
            'stdItem' => [
                'Manufacturer' => [
                    'Code' => 'OPT',
                    'UUID' => $manufacturerUuid,
                ],
            ],
            'entity_tag_map' => $sharedTags,
        ],
        'Raw' => [],
    ];

    Storage::disk('scunpacked')->put('items/opt-first.json', json_encode($firstPayload, JSON_THROW_ON_ERROR));

    (new ImportItemData($version->id, 'items/opt-first.json', $labels))->handle();

    $firstItem = Item::query()->firstWhere('uuid', $firstItemUuid);
    $firstData = ItemData::query()
        ->where('item_id', $firstItem->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($firstData)->not->toBeNull();
    $firstData->load('entityTags');
    $firstTagIds = $firstData->entityTags->pluck('id')->sort()->values()->all();

    $secondItemUuid = fake()->uuid();
    $secondPayload = $firstPayload;
    $secondPayload['Item']['reference'] = $secondItemUuid;
    $secondPayload['Item']['className'] = 'OPT_Second_Item';
    $secondPayload['Item']['itemName'] = 'Optimized Item Two';
    $secondPayload['Item']['entity_tag_map'] = array_reverse($sharedTags);

    Storage::disk('scunpacked')->put('items/opt-second.json', json_encode($secondPayload, JSON_THROW_ON_ERROR));

    (new ImportItemData($version->id, 'items/opt-second.json', $labels))->handle();

    $secondItem = Item::query()->firstWhere('uuid', $secondItemUuid);
    $secondData = ItemData::query()
        ->where('item_id', $secondItem->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($secondData)->not->toBeNull();
    $secondData->load('entityTags');

    $secondTagIds = $secondData->entityTags->pluck('id')->sort()->values()->all();

    expect(EntityTag::query()->count())->toBe(3)
        ->and($firstTagIds)->toBe($secondTagIds)
        ->and($secondData->entityTags->pluck('name')->sort()->values()->all())->toBe(['Existing One', 'Existing Two', 'New One']);
});

it('syncs commodities from ResourceContainer DefaultComposition', function (): void {
    Storage::fake('scunpacked');

    GameLabel::factory()->asItemDescTest()->create();
    $labels = new Labels;

    $version = GameVersion::query()->create([
        'code' => '3.26.0',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $manufacturerUuid = fake()->uuid();
    Manufacturer::query()->create([
        'uuid' => $manufacturerUuid,
        'name' => 'Test Manufacturer',
        'code' => 'TMFR',
    ]);

    $commodityUuid1 = fake()->uuid();
    $commodityUuid2 = fake()->uuid();
    $commodity1 = Commodity::factory()->create(['uuid' => $commodityUuid1, 'name' => 'Aphorite']);
    $commodity2 = Commodity::factory()->create(['uuid' => $commodityUuid2, 'name' => 'EVA Fuel']);

    $itemUuid = fake()->uuid();
    $payload = [
        'Item' => [
            'reference' => $itemUuid,
            'className' => 'TST_Commodity_Item',
            'itemName' => 'Commodity Item',
            'type' => 'Cargo',
            'stdItem' => [
                'Manufacturer' => [
                    'Code' => 'TMFR',
                    'UUID' => $manufacturerUuid,
                ],
                'ResourceContainer' => [
                    'DefaultComposition' => [
                        ['Entry' => $commodityUuid1, 'Weight' => 1],
                        ['Entry' => $commodityUuid2, 'Weight' => 0.5],
                    ],
                ],
            ],
        ],
        'Raw' => [],
    ];

    Storage::disk('scunpacked')->put('items/commodity.json', json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportItemData($version->id, 'items/commodity.json', $labels))->handle();

    $data = ItemData::query()
        ->whereHas('item', fn ($q) => $q->where('uuid', $itemUuid))
        ->where('game_version_id', $version->id)
        ->first();

    expect($data)->not->toBeNull();

    $commodities = $data->commodities;
    expect($commodities)->toHaveCount(2)
        ->and($commodities->pluck('uuid')->sort()->values()->all())->toBe(collect([$commodityUuid1, $commodityUuid2])->sort()->values()->all());

    // Re-run with one commodity removed to verify sync is idempotent
    $payload['Item']['stdItem']['ResourceContainer']['DefaultComposition'] = [
        ['Entry' => $commodityUuid1, 'Weight' => 1],
    ];
    Storage::disk('scunpacked')->put('items/commodity.json', json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportItemData($version->id, 'items/commodity.json', $labels))->handle();

    $data->refresh();
    $commodities = $data->commodities;
    expect($commodities)->toHaveCount(1)
        ->and($commodities->first()->uuid)->toBe($commodityUuid1);
});

it('syncs commodities with unknown uuids gracefully', function (): void {
    Storage::fake('scunpacked');

    GameLabel::factory()->asItemDescTest()->create();
    $labels = new Labels;

    $version = GameVersion::query()->create([
        'code' => '3.26.1',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $manufacturerUuid = fake()->uuid();
    Manufacturer::query()->create([
        'uuid' => $manufacturerUuid,
        'name' => 'Test Manufacturer',
        'code' => 'TMFR',
    ]);

    $itemUuid = fake()->uuid();
    $payload = [
        'Item' => [
            'reference' => $itemUuid,
            'className' => 'TST_Unknown_Commodity',
            'itemName' => 'Unknown Commodity Item',
            'type' => 'Cargo',
            'stdItem' => [
                'Manufacturer' => [
                    'Code' => 'TMFR',
                    'UUID' => $manufacturerUuid,
                ],
                'ResourceContainer' => [
                    'DefaultComposition' => [
                        ['Entry' => '00000000-0000-0000-0000-000000000000', 'Weight' => 1],
                    ],
                ],
            ],
        ],
        'Raw' => [],
    ];

    Storage::disk('scunpacked')->put('items/unknown-commodity.json', json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportItemData($version->id, 'items/unknown-commodity.json', $labels))->handle();

    $data = ItemData::query()
        ->whereHas('item', fn ($q) => $q->where('uuid', $itemUuid))
        ->where('game_version_id', $version->id)
        ->first();

    expect($data)->not->toBeNull()
        ->and($data->commodities)->toHaveCount(0);
});
