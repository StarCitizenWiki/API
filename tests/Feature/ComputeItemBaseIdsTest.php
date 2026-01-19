<?php

declare(strict_types=1);

use App\Jobs\Game\ComputeItemBaseIds as ComputeItemBaseIdsJob;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->manufacturer = Manufacturer::query()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);
});

it('fails when the requested game version does not exist', function (): void {
    Queue::fake();

    $this->artisan('game:compute-item-base-ids', ['--game-version' => 'missing'])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('Game version "missing" does not exist.');

    Queue::assertNothingPushed();
});

it('dispatches a compute job for the default game version', function (): void {
    Queue::fake();

    $version = GameVersion::query()->create([
        'code' => '3.24.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $this->artisan('game:compute-item-base-ids')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Dispatched item base id compute job for version 3.24.0-LIVE.');

    Queue::assertPushed(ComputeItemBaseIdsJob::class);
});

it('computes base ids from class name patterns', function (): void {
    $version = GameVersion::query()->create([
        'code' => '3.24.1-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $baseUuid = fake()->uuid();
    $baseItem = Item::query()->create(['uuid' => $baseUuid]);
    $baseData = ItemData::query()->create([
        'item_id' => $baseItem->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Acme Jacket Base',
        'class_name' => 'acme_jacket_01_01_01',
        'type' => 'Char_Clothing_Torso_1',
        'data' => [],
    ]);

    $variantUuid = fake()->uuid();
    $variantItem = Item::query()->create(['uuid' => $variantUuid]);
    $variantData = ItemData::query()->create([
        'item_id' => $variantItem->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Acme Jacket Variant',
        'class_name' => 'acme_jacket_01_01_02',
        'type' => 'Char_Clothing_Torso_1',
        'data' => [],
    ]);

    (new ComputeItemBaseIdsJob($version->id))->handle();

    expect($variantData->fresh()->base_id)->toBe($baseData->id)
        ->and($baseData->fresh()->base_id)->toBeNull();
});

it('computes base ids from stdItem tags when class names do not match', function (): void {
    $version = GameVersion::query()->create([
        'code' => '3.24.2-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $tagBaseUuid = fake()->uuid();
    $baseItem = Item::query()->create(['uuid' => $tagBaseUuid]);
    $baseData = ItemData::query()->create([
        'item_id' => $baseItem->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Kap Light Helmet',
        'class_name' => 'kap_light_helmet_base',
        'classification' => 'FPS.Armor.Helmet',
        'data' => [
            'stdItem' => [
                'Tags' => ['kap_light', 'Set_01', 'Color_01', 'Helmet'],
            ],
        ],
    ]);

    $variantUuid = fake()->uuid();
    $variantItem = Item::query()->create(['uuid' => $variantUuid]);
    $variantData = ItemData::query()->create([
        'item_id' => $variantItem->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Kap Light Helmet Rogue',
        'class_name' => 'kap_light_helmet_rogue',
        'classification' => 'FPS.Armor.Helmet',
        'data' => [
            'stdItem' => [
                'Tags' => ['kap_light', 'Set_01', 'Color_02', 'Helmet'],
            ],
        ],
    ]);

    (new ComputeItemBaseIdsJob($version->id))->handle();

    expect($variantData->fresh()->base_id)->toBe($baseData->id)
        ->and($baseData->fresh()->base_id)->toBeNull();
});
