<?php

declare(strict_types=1);

use App\Models\Game\BlueprintData;
use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

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
            'reference' => fake()->uuid(),
            'name' => 'Test Manufacturer',
            'code' => 'TST',
        ],
    ], JSON_THROW_ON_ERROR));

    Storage::disk('scunpacked')->put('tags.json', json_encode([
        fake()->uuid() => 'Test Tag',
    ], JSON_THROW_ON_ERROR));

    Storage::disk('scunpacked')->put('resource-types.json', json_encode([
        [
            'uuid' => $this->resourceTypeUuid,
            'key' => 'test_resource',
            'name' => 'Test Resource',
        ],
    ], JSON_THROW_ON_ERROR));
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
            'uuid' => fake()->uuid(),
            'key' => 'BP_SYNC_ONLY',
            'category_uuid' => fake()->uuid(),
            'output' => [
                'uuid' => fake()->uuid(),
                'class' => 'sync_only_output',
                'name' => 'Sync Only Output',
            ],
            'availability' => [
                'default' => true,
            ],
            'tiers' => [
                [
                    'tier_index' => 0,
                    'craft_time_seconds' => 45,
                    'requirements' => [
                        'kind' => 'root',
                        'children' => [
                            [
                                'kind' => 'resource',
                                'uuid' => $this->resourceTypeUuid,
                                'name' => 'Test Resource',
                                'quantity_scu' => 1.5,
                                'min_quality' => 0,
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
        '--skip-compute-item-base-ids' => true,
        '--skip-backfill-shipmatrix-ids' => true,
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
        '--skip-compute-item-base-ids' => true,
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
        '--skip-vehicles' => true,
        '--skip-compute-item-base-ids' => true,
        '--skip-backfill-shipmatrix-ids' => true,
    ])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('blueprints.json not found in scunpacked storage.');

    Bus::assertNothingBatched();
});
