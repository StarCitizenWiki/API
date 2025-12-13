<?php

declare(strict_types=1);

use App\Jobs\Game\ImportItemData;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\ItemDescriptionData;
use App\Models\Game\ItemTranslation;
use App\Models\Game\Manufacturer;
use App\Models\System\Language;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

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

    File::ensureDirectoryExists(storage_path('app/api/ScToolBoxLocales/chinese_(simplified)'));
    File::ensureDirectoryExists(storage_path('app/api/scunpacked-data'));
    File::put(storage_path('app/api/ScToolBoxLocales/chinese_(simplified)/global.ini'), "item_Desc_test=中文描述\n");
    File::put(storage_path('app/api/scunpacked-data/labels.json'), json_encode([], JSON_THROW_ON_ERROR));

    $version = GameVersion::query()->create([
        'code' => '3.23.1',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $manufacturer = Manufacturer::query()->create([
        'uuid' => 'uuid-rsi',
        'name' => 'Roberts Space Industries',
        'code' => 'RSI',
    ]);

    $payload = [
        'Item' => [
            'reference' => 'uuid-item',
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

    (new ImportItemData($version->id, 'items/test.json'))->handle();

    $item = Item::query()->firstWhere('uuid', 'uuid-item');
    expect($item)->not->toBeNull();

    $data = ItemData::query()
        ->where('item_id', $item->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($data)->not->toBeNull();
    expect($data->manufacturer_id)->toBe($manufacturer->id);
    expect($data->name)->toBe('Test Item');
    expect($data->data)->toHaveKey('tags');
    expect($data->data)->not->toHaveKey('name');

    $descriptionData = ItemDescriptionData::query()
        ->where('item_id', $item->id)
        ->orderBy('name')
        ->get();

    expect($descriptionData)->toHaveCount(2);
    expect($descriptionData->first()->name)->toBe('Damage');
    expect($descriptionData->first()->value)->toBe('10');

    $english = ItemTranslation::query()
        ->where('item_data_id', $data->id)
        ->where('locale_code', Language::ENGLISH)
        ->first();

    expect($english)->not->toBeNull();
    expect($english->translation)->toBe('English description');

    $chinese = ItemTranslation::query()
        ->where('item_data_id', $data->id)
        ->where('locale_code', Language::CHINESE)
        ->first();

    expect($chinese)->not->toBeNull();
    expect($chinese->translation)->toBe('中文描述');

    // Re-run with updated payload to verify upsert behaviour
    $payload['Item']['grade'] = 4;
    $payload['Raw']['Entity']['Components']['SAttachableComponentParams']['AttachDef']['Localization']['English']['Description'] = 'Updated English';

    Storage::disk('scunpacked')->put('items/test.json', json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportItemData($version->id, 'items/test.json'))->handle();

    $data->refresh();
    expect($data->grade)->toBe(4);

    $english->refresh();
    expect($english->translation)->toBe('Updated English');
});

it('skips chinese translation when the key is missing and uses stdItem manufacturer fallback', function (): void {
    Storage::fake('scunpacked');

    File::ensureDirectoryExists(storage_path('app/api/ScToolBoxLocales/chinese_(simplified)'));
    File::ensureDirectoryExists(storage_path('app/api/scunpacked-data'));
    File::put(storage_path('app/api/ScToolBoxLocales/chinese_(simplified)/global.ini'), "other_key=值\n");
    File::put(storage_path('app/api/scunpacked-data/labels.json'), json_encode([], JSON_THROW_ON_ERROR));

    $version = GameVersion::query()->create([
        'code' => '3.23.2',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $manufacturer = Manufacturer::query()->create([
        'uuid' => 'uuid-unknown',
        'name' => 'Unknown Manufacturer',
        'code' => 'UNKN',
    ]);

    $payload = [
        'Item' => [
            'reference' => 'uuid-unknown-item',
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
                ],
                'Description' => 'Fallback description',
            ],
        ],
        'Raw' => [
            'Entity' => [
                'Components' => [
                    'SAttachableComponentParams' => [
                        'AttachDef' => [
                            'Localization__Description' => '@missing_key',
                            'Localization' => [
                                '__Description' => '@missing_key',
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

    (new ImportItemData($version->id, 'items/unknown.json'))->handle();

    $item = Item::query()->firstWhere('uuid', 'uuid-unknown-item');
    expect($item)->not->toBeNull();

    $data = ItemData::query()
        ->where('item_id', $item->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($data)->not->toBeNull();
    expect($data->manufacturer_id)->toBe($manufacturer->id);

    $translations = ItemTranslation::query()
        ->where('item_data_id', $data->id)
        ->get();

    expect($translations->where('locale_code', Language::CHINESE))->toHaveCount(0);
    expect($translations->where('locale_code', Language::ENGLISH))->toHaveCount(1);
});
