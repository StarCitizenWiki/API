<?php

declare(strict_types=1);

use App\Jobs\Game\AddBatchJobs;
use App\Jobs\Game\ImportItemData;
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
    Storage::disk('scunpacked')->put('resources/locations.json', json_encode([], JSON_THROW_ON_ERROR));

    // game:import-resource-data hard-fails without starmap data, so provide a
    // minimal starmap fixture that the inline starmap importer can ingest.
    Storage::disk('scunpacked')->put('starmap.json', json_encode([
        [
            'UUID' => '11111111-1111-1111-1111-111111111111',
            'Name' => 'Port Olisar',
            'Description' => 'A prominent space station.',
            'Type' => ['Name' => 'Manmade', 'Classification' => 'Manmade'],
            'Size' => 1.0,
            'IsScannable' => true,
            'BlockTravel' => false,
            'HideInStarmap' => false,
            'HideInWorld' => false,
            'OnlyShowWhenParentSelected' => false,
            'Jurisdiction' => ['Name' => 'UEE'],
            'Affiliation' => ['DisplayName' => 'United Empire of Earth'],
            'RespawnLocationType' => 'Hospital',
            'Amenities' => [],
        ],
    ], JSON_THROW_ON_ERROR));

    Storage::disk('scunpacked')->put('blueprints.json', json_encode([], JSON_THROW_ON_ERROR));
});

it('imports blueprints for the selected game version', function (): void {
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

    Bus::fake();

    $this->artisan('game:sync-data', ['--game-version' => $version->code])
        ->assertExitCode(Command::SUCCESS);

    expect(BlueprintData::query()->where('key', 'BP_SYNC_ONLY')->exists())->toBeTrue();
});

it('imports manufacturers, tags and blueprints even without item or vehicle files', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.1-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    Bus::fake();

    $this->artisan('game:sync-data', ['--game-version' => $version->code])
        ->assertExitCode(Command::SUCCESS);

    expect(Manufacturer::query()->where('code', 'TST')->exists())->toBeTrue();
});

it('fails before dispatching the import batch when blueprint import fails', function (): void {
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

    // Break blueprints so the command aborts before the batch is dispatched.
    File::put(config('translations.labels_json'), 'not-json');

    $this->artisan('game:sync-data', ['--game-version' => $version->code])
        ->assertExitCode(Command::FAILURE);

    Bus::assertNotDispatched(ImportItemData::class);
    Bus::assertNotDispatched(ImportVehicleData::class);
    Bus::assertNotDispatched(AddBatchJobs::class);
});

it('dispatches item and vehicle import jobs in a single batch', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.1.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    Storage::disk('scunpacked')->put('items/weapon.json', json_encode([
        'Item' => ['reference' => fake()->uuid()],
    ], JSON_THROW_ON_ERROR));
    Storage::disk('scunpacked')->put('ships/aurora.json', json_encode([
        'ship' => ['reference' => fake()->uuid()],
    ], JSON_THROW_ON_ERROR));

    Bus::fake();

    $this->artisan('game:sync-data', ['--game-version' => $version->code])
        ->assertExitCode(Command::SUCCESS);

    Bus::assertBatchCount(1);

    // Items and vehicles merge into a single batch (previously two), wrapped in
    // AddBatchJobs loaders. Locking in the single-batch dispatch guards the merge.
    Bus::assertBatched(fn ($batch): bool => $batch->jobs->every(
        static fn ($job): bool => $job instanceof AddBatchJobs
    ));
});

it('prompts for a game version when none is provided and none exists', function (): void {
    $this->artisan('game:sync-data')
        ->expectsOutputToContain('No game versions exist.')
        ->assertExitCode(Command::FAILURE);
});

it('fails when the provided game version does not exist', function (): void {
    $this->artisan('game:sync-data', ['--game-version' => '9.9.9-LIVE'])
        ->expectsOutputToContain('does not exist.')
        ->assertExitCode(Command::FAILURE);
});
