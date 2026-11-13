<?php

declare(strict_types=1);

use App\Jobs\Game\ImportItemData;
use App\Models\Game\EntityTag;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\ItemDescriptionData;
use App\Models\Game\Manufacturer;
use App\Models\System\Language;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('scunpacked');

    $this->version = GameVersion::factory()->create();
    $this->manufacturer = Manufacturer::factory()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Test Manufacturer',
        'code' => 'TST',
    ]);
});

function dispatchItemImport(int $versionId, array $payload, string $file = 'items/test.json'): void
{
    Storage::disk('scunpacked')->put($file, json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportItemData($versionId, $file))->handle();
}

it('does not create item data when its batch is cancelled', function (): void {
    $payload = itemPayload($this->manufacturer->uuid);
    Storage::disk('scunpacked')->put('items/test.json', json_encode($payload, JSON_THROW_ON_ERROR));
    $job = new ImportItemData($this->version->id, 'items/test.json');
    [$job] = $job->withFakeBatch(cancelledAt: now()->toImmutable());

    $job->handle();

    expect(ItemData::query()->count())->toBe(0);
});

it('runs the import writes inside a transaction', function (): void {
    $baseline = DB::transactionLevel();
    $writes = [];
    $events = [];
    Event::listen(TransactionBeginning::class, function () use (&$events): void {
        $events[] = 'begin';
    });
    Event::listen(TransactionCommitted::class, function () use (&$events): void {
        $events[] = 'commit';
    });
    DB::listen(function ($query) use (&$writes): void {
        if (preg_match('/^(insert|update|delete)\b/i', $query->sql)) {
            $writes[] = [$query->sql, $query->connection->transactionLevel()];
        }
    });

    $payload = itemPayload($this->manufacturer->uuid);
    $payload['Item']['reference'] = fake()->uuid();
    dispatchItemImport($this->version->id, $payload);

    expect($events)->toContain('begin', 'commit');
    $matching = array_values(array_filter($writes, fn ($write) => str_contains($write[0], 'game_items')));
    expect($matching)->not->toBeEmpty();
    foreach ($matching as $write) {
        expect($write[1])->toBeGreaterThan($baseline);
    }
    $matching = array_values(array_filter($writes, fn ($write) => str_contains($write[0], 'game_item_data')));
    expect($matching)->not->toBeEmpty();
    foreach ($matching as $write) {
        expect($write[1])->toBeGreaterThan($baseline);
    }
});

it('creates item data on first import', function (): void {
    $payload = itemPayload($this->manufacturer->uuid);

    dispatchItemImport($this->version->id, $payload);

    $item = Item::query()->where('uuid', $payload['Item']['reference'])->first();
    expect($item)->not->toBeNull();

    $itemData = ItemData::query()
        ->where('item_id', $item->id)
        ->where('game_version_id', $this->version->id)
        ->first();

    expect($itemData)->not->toBeNull()
        ->and($itemData->name)->toBe('Test Weapon')
        ->and($itemData->type)->toBe('WeaponPersonal')
        ->and($itemData->class_name)->toBe('TEST_WEAPON')
        ->and($itemData->data)->toBeArray();
});

it('persists the row when source data changes', function (): void {
    $payload = itemPayload($this->manufacturer->uuid);

    dispatchItemImport($this->version->id, $payload);

    // Simulate a genuine data change in the source item.
    $payload['Item']['name'] = 'Updated Weapon Name';
    dispatchItemImport($this->version->id, $payload);

    $itemData = ItemData::query()
        ->where('game_version_id', $this->version->id)
        ->first();

    // The guard must let a genuine change through and persist it.
    expect($itemData->name)->toBe('Updated Weapon Name');
});

/**
 * Report whether the job's static item cache still holds the given uuid.
 */
function itemImportCacheHas(string $uuid): bool
{
    $cache = new ReflectionProperty(ImportItemData::class, 'itemCache');

    return array_key_exists($uuid, $cache->getValue());
}

/**
 * Throw once when the import writes the given table (and column), to inject a
 * failure at a specific item chain stage.
 */
function failItemImportAtWrite(string $table, ?string $column = null): void
{
    $armed = true;

    DB::listen(function ($query) use ($table, $column, &$armed): void {
        if (! $armed) {
            return;
        }
        if (preg_match('/^(insert|update|delete)\b/i', $query->sql) !== 1 || ! str_contains($query->sql, "\"{$table}\"")) {
            return;
        }
        if ($column !== null && ! str_contains($query->sql, "\"{$column}\"")) {
            return;
        }
        $armed = false;

        throw new RuntimeException("injected {$table} write failure");
    });
}

it('recreates a new parent item on a same-process retry after a rolled-back import', function (): void {
    $itemUuid = '12121212-1212-1212-1212-121212121212';
    $payload = itemPayload('99999999-9999-9999-9999-999999999999');
    $payload['Item']['reference'] = $itemUuid;

    // The unknown manufacturer throws after the parent item was already created in the transaction.
    expect(fn () => dispatchItemImport($this->version->id, $payload, 'items/retry-new.json'))
        ->toThrow(RuntimeException::class);

    expect(Item::query()->count())->toBe(0)
        ->and(ItemData::query()->count())->toBe(0)
        ->and(itemImportCacheHas($itemUuid))->toBeFalse();

    Manufacturer::factory()->create([
        'uuid' => '99999999-9999-9999-9999-999999999999',
        'name' => 'Retry Manufacturer',
        'code' => 'RTR',
    ]);

    dispatchItemImport($this->version->id, $payload, 'items/retry-new.json');

    $item = Item::query()->where('uuid', $itemUuid)->firstOrFail();
    expect($item->slug)->not->toBeNull()
        ->and(ItemData::query()
            ->where('item_id', $item->id)
            ->where('game_version_id', $this->version->id)
            ->exists())->toBeTrue();
});

it('drops uncommitted slug, translation, and description writes from the item cache when a late stage fails', function (): void {
    $itemUuid = '13131313-1313-1313-1313-131313131313';
    $item = Item::factory()->create(['uuid' => $itemUuid, 'slug' => null]);

    $payload = itemPayload($this->manufacturer->uuid);
    $payload['Item']['reference'] = $itemUuid;
    $payload['Item']['name'] = 'Cached Weapon';
    $payload['Item']['stdItem']['DescriptionData'] = ['Damage' => '10'];
    $payload['Item']['stdItem']['DescriptionText'] = 'English description';
    $payload['Raw'] = [
        'Entity' => ['Components' => ['SAttachableComponentParams' => ['AttachDef' => ['Localization' => ['__Description' => '@items_Test_Weapon_desc']]]]],
    ];

    // Fail while persisting the item translation, after the data upsert, slug, and description writes.
    failItemImportAtWrite('game_items', 'translation');

    Storage::disk('scunpacked')->put('items/retry-existing.json', json_encode($payload, JSON_THROW_ON_ERROR));

    dispatchItemImportShouldFail($this->version->id, $payload, 'items/retry-existing.json', 'injected game_items write failure');

    expect($item->refresh()->slug)->toBeNull()
        ->and($item->getTranslation('translation', Language::ENGLISH, false))->toBeEmpty()
        ->and(ItemDescriptionData::query()->count())->toBe(0)
        ->and(ItemData::query()->count())->toBe(0)
        ->and(itemImportCacheHas($itemUuid))->toBeFalse();

    dispatchItemImport($this->version->id, $payload, 'items/retry-existing.json');

    expect($item->refresh()->slug)->toBe('cached-weapon')
        ->and($item->getTranslation('translation', Language::ENGLISH, false))->toBe('English description')
        ->and(ItemDescriptionData::query()->where('item_id', $item->id)->count())->toBe(1)
        ->and(ItemData::query()->where('item_id', $item->id)->count())->toBe(1);
});

/**
 * Dispatch an item import that is expected to fail with the given message.
 */
function dispatchItemImportShouldFail(int $versionId, array $payload, string $file, string $message): void
{
    Storage::disk('scunpacked')->put($file, json_encode($payload, JSON_THROW_ON_ERROR));

    expect(fn () => (new ImportItemData($versionId, $file))->handle())->toThrow(RuntimeException::class, $message);
}

it('invalidates the entity tag lookup so a later import does not sync a rolled-back tag', function (): void {
    $tagUuid = '14141414-1414-1414-1414-141414141414';

    $payloadA = itemPayload($this->manufacturer->uuid);
    $payloadA['Item']['reference'] = '15151515-1515-1515-1515-151515151515';
    $payloadA['Item']['entity_tag_map'] = [
        ['tag' => $tagUuid, 'name' => 'RollbackTag'],
    ];

    // Fail while syncing the tag pivot, after the missing entity tag was created in the transaction.
    failItemImportAtWrite('game_item_data_entity_tag');

    dispatchItemImportShouldFail($this->version->id, $payloadA, 'items/tag-rollback.json', 'injected game_item_data_entity_tag write failure');

    $lookup = new ReflectionProperty(ImportItemData::class, 'entityTagsLookup');
    expect(EntityTag::query()->count())->toBe(0)
        ->and($lookup->getValue())->toBeNull();

    // Same process, another item carrying the same tag: it must be recreated, not reused from the lookup.
    $payloadB = itemPayload($this->manufacturer->uuid);
    $payloadB['Item']['reference'] = '16161616-1616-1616-1616-161616161616';
    $payloadB['Item']['entity_tag_map'] = [
        ['tag' => $tagUuid, 'name' => 'RollbackTag'],
    ];

    dispatchItemImport($this->version->id, $payloadB, 'items/tag-retry.json');
    $itemDataB = ItemData::query()
        ->whereHas('item', fn ($query) => $query->where('uuid', '16161616-1616-1616-1616-161616161616'))
        ->firstOrFail();

    expect($itemDataB->entityTags->pluck('uuid'))->toContain($tagUuid);
});

/**
 * Build a minimal, valid item payload.
 */
function itemPayload(string $manufacturerUuid): array
{
    return [
        'Item' => [
            'reference' => '11111111-1111-1111-1111-111111111111',
            'className' => 'TEST_WEAPON',
            'type' => 'WeaponPersonal',
            'subType' => 'Medium',
            'classification' => 'FPS.Weapon',
            'size' => 2,
            'grade' => 1,
            'name' => 'Test Weapon',
            'stdItem' => [
                'Manufacturer' => ['UUID' => $manufacturerUuid],
                'Mass' => 1.5,
                'Rarity' => 'Common',
            ],
        ],
    ];
}
