<?php

declare(strict_types=1);

use App\Jobs\Game\AddBatchJobs;
use App\Jobs\Game\ComputeItemSetItems as ComputeItemSetItemsJob;
use App\Jobs\Game\ComputeItemVariantGroups as ComputeItemVariantGroupsJob;
use App\Jobs\Game\ImportCommodityPrices as ImportCommodityPricesJob;
use App\Jobs\Game\ImportItemData;
use App\Jobs\Game\ImportItemPrices as ImportItemPricesJob;
use App\Jobs\Game\ImportVehicleData;
use App\Models\Game\BlueprintData;
use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function (): void {
    Storage::fake('scunpacked');

    $this->fixturesPath = storage_path('framework/testing/'.Str::uuid());
    File::ensureDirectoryExists($this->fixturesPath);

    config()->set('translations.labels_json', "{$this->fixturesPath}/labels.json");
    config()->set('translations.sources.zh_CN', "{$this->fixturesPath}/zh.ini");
    config()->set('translations.sources.de_DE', "{$this->fixturesPath}/de.ini");

    File::put(
        "{$this->fixturesPath}/labels.json",
        json_encode([
            'item.alpha' => 'Alpha',
        ], JSON_THROW_ON_ERROR)
    );
    File::put("{$this->fixturesPath}/zh.ini", "item.alpha=\"Alpha ZH\"\n");
    File::put("{$this->fixturesPath}/de.ini", "item.alpha=\"Alpha DE\"\n");

    $this->resourceTypeUuid = fake()->uuid();

    Storage::disk('scunpacked')->put('manufacturers.json', json_encode([
        [
            'Reference' => fake()->uuid(),
            'Name' => 'Test Manufacturer',
            'Code' => 'TST',
        ],
    ], JSON_THROW_ON_ERROR));

    Storage::disk('scunpacked')->put('tags.json', json_encode([
        fake()->uuid() => 'Test Tag',
    ], JSON_THROW_ON_ERROR));

    Storage::disk('scunpacked')->put('resources/commodities.json', json_encode([
        [
            'UUID' => $this->resourceTypeUuid,
            'Key' => 'test_resource',
            'Name' => 'Test Resource',
            'Description' => 'A test resource.',
            'RefinedVersionUUID' => null,
            'RefinedVersionName' => null,
            'ValidateDefaultCargoBox' => true,
            'HasDefaultCargoContainers' => false,
            'CargoContainers' => [],
            'QualityDistributionUUID' => null,
            'QualityLocationOverrideUUID' => null,
            'Tier' => null,
        ],
    ], JSON_THROW_ON_ERROR));

    Storage::disk('scunpacked')->put('resources/resources.json', json_encode([], JSON_THROW_ON_ERROR));
});

it('imports blueprints when an explicit game version is provided', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    Storage::disk('scunpacked')->put('blueprints.json', json_encode([
        [
            'UUID' => fake()->uuid(),
            'Key' => 'BP_SYNC_ONLY',
            'Kind' => 'creation',
            'CategoryUUID' => fake()->uuid(),
            'Output' => [
                'UUID' => fake()->uuid(),
                'Class' => 'sync_only_output',
                'Name' => 'Sync Only Output',
            ],
            'Availability' => [
                'Default' => true,
            ],
            'Tiers' => [
                [
                    'CraftTimeSeconds' => 45,
                    'Requirements' => [
                        'Kind' => 'root',
                        'Children' => [
                            [
                                'Kind' => 'resource',
                                'UUID' => $this->resourceTypeUuid,
                                'Name' => 'Test Resource',
                                'QuantityScu' => 1.5,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ], JSON_THROW_ON_ERROR));

    $this->artisan('game:sync-data', [
        '--game-version' => $version->code,
        '--skip-items' => true,
        '--skip-vehicles' => true,
        '--skip-resources' => true,
        '--skip-compute-item-groups' => true,
        '--skip-backfill-shipmatrix-ids' => true,
        '--skip-prices' => true,
    ])->assertExitCode(Command::SUCCESS);

    expect(BlueprintData::query()->where('key', 'BP_SYNC_ONLY')->exists())->toBeTrue();
});

it('syncs non-versioned data without requiring a game version when item and vehicle imports are skipped', function (): void {
    Storage::disk('scunpacked')->put('blueprints.json', json_encode([
        [
            'uuid' => fake()->uuid(),
            'key' => 'BP_SKIPPED_WITHOUT_VERSION',
            'category_uuid' => fake()->uuid(),
            'output' => [
                'uuid' => fake()->uuid(),
            ],
        ],
    ], JSON_THROW_ON_ERROR));

    $this->artisan('game:sync-data', [
        '--skip-items' => true,
        '--skip-vehicles' => true,
        '--skip-starmap' => true,
        '--skip-resources' => true,
        '--skip-missions' => true,
        '--skip-compute-item-groups' => true,
        '--skip-backfill-shipmatrix-ids' => true,
    ])->assertExitCode(Command::SUCCESS);

    expect(Manufacturer::query()->where('code', 'TST')->exists())->toBeTrue()
        ->and(BlueprintData::query()->where('key', 'BP_SKIPPED_WITHOUT_VERSION')->exists())->toBeFalse();
});

it('fails before dispatching versioned imports when blueprint import fails', function (): void {
    Bus::fake();

    $version = GameVersion::factory()->create([
        'code' => '4.0.1-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    Storage::disk('scunpacked')->put('items/test.json', json_encode([
        'Item' => [
            'reference' => fake()->uuid(),
        ],
    ], JSON_THROW_ON_ERROR));

    $this->artisan('game:sync-data', [
        '--game-version' => $version->code,
        '--skip-items' => true,
        '--skip-vehicles' => true,
        '--skip-resources' => true,
        '--skip-compute-item-groups' => true,
        '--skip-backfill-shipmatrix-ids' => true,
    ])->assertExitCode(Command::FAILURE);

    Bus::assertNothingBatched();
    Bus::assertNothingDispatched();
    Bus::assertNotDispatched(AddBatchJobs::class);
    Bus::assertNotDispatched(ImportItemData::class);
    Bus::assertNotDispatched(ImportVehicleData::class);
    Bus::assertNotDispatched(ComputeItemVariantGroupsJob::class);
    Bus::assertNotDispatched(ComputeItemSetItemsJob::class);
});

it('imports missions when game version is provided', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.1.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    Storage::disk('scunpacked')->put('contracts/test_mission.json', json_encode([
        'UUID' => fake()->uuid(),
        'Key' => 'TestMission',
    ], JSON_THROW_ON_ERROR));

    Storage::disk('scunpacked')->put('blueprints.json', json_encode([], JSON_THROW_ON_ERROR));

    $this->artisan('game:sync-data', [
        '--game-version' => $version->code,
        '--skip-items' => true,
        '--skip-vehicles' => true,
        '--skip-starmap' => true,
        '--skip-resources' => true,
        '--skip-compute-item-groups' => true,
        '--skip-backfill-shipmatrix-ids' => true,
        '--skip-prices' => true,
    ])->assertExitCode(Command::SUCCESS);
});

it('skips missions when --skip-missions is passed', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.1.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    Storage::disk('scunpacked')->put('blueprints.json', json_encode([], JSON_THROW_ON_ERROR));

    $this->artisan('game:sync-data', [
        '--game-version' => $version->code,
        '--skip-items' => true,
        '--skip-vehicles' => true,
        '--skip-starmap' => true,
        '--skip-resources' => true,
        '--skip-missions' => true,
        '--skip-compute-item-groups' => true,
        '--skip-backfill-shipmatrix-ids' => true,
        '--skip-prices' => true,
    ])->assertExitCode(Command::SUCCESS);
});

it('dispatches the UEX price import after a versioned game data sync', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.2.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    Storage::disk('scunpacked')->put('blueprints.json', json_encode([], JSON_THROW_ON_ERROR));

    Bus::fake();

    $this->artisan('game:sync-data', [
        '--game-version' => $version->code,
        '--skip-items' => true,
        '--skip-vehicles' => true,
        '--skip-starmap' => true,
        '--skip-resources' => true,
        '--skip-missions' => true,
        '--skip-compute-item-groups' => true,
        '--skip-backfill-shipmatrix-ids' => true,
    ])->assertExitCode(Command::SUCCESS);

    Bus::assertBatched(function ($batch): bool {
        return $batch->jobs->contains(static fn ($job): bool => $job instanceof ImportItemPricesJob)
            && $batch->jobs->contains(static fn ($job): bool => $job instanceof ImportCommodityPricesJob);
    });
});

it('skips the UEX price import when --skip-prices is passed', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.2.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    Storage::disk('scunpacked')->put('blueprints.json', json_encode([], JSON_THROW_ON_ERROR));

    Bus::fake();

    $this->artisan('game:sync-data', [
        '--game-version' => $version->code,
        '--skip-items' => true,
        '--skip-vehicles' => true,
        '--skip-starmap' => true,
        '--skip-resources' => true,
        '--skip-missions' => true,
        '--skip-prices' => true,
        '--skip-compute-item-groups' => true,
        '--skip-backfill-shipmatrix-ids' => true,
    ])->assertExitCode(Command::SUCCESS);

    Bus::assertNothingBatched();
});
