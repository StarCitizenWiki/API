<?php

declare(strict_types=1);

use App\Jobs\Game\ImportItemData;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
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
