<?php

declare(strict_types=1);

use App\Console\Commands\Game\SyncGameData;
use App\Jobs\Game\AddBatchJobs;
use App\Jobs\Game\ImportItemData;
use App\Jobs\Game\ImportVehicleData;
use App\Jobs\Game\SyncVehicleInstalledItems;
use App\Models\Game\BlueprintData;
use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\VehicleData;
use Illuminate\Bus\Batch;
use Illuminate\Bus\BatchRepository;
use Illuminate\Console\Command;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function queuedBatchId(string $queue): string
{
    $payload = DB::table('jobs')->where('queue', $queue)->value('payload');
    expect($payload)->not->toBeNull();
    $job = unserialize(json_decode($payload, true, 512, JSON_THROW_ON_ERROR)['data']['command']);

    return $job->batchId;
}

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

it('registers every loader before dispatching a large batch', function (): void {
    Bus::fake();
    $jobs = collect(array_fill(0, 100001, new ImportVehicleData(1, 'ships/test.json')));
    $method = new ReflectionMethod(SyncGameData::class, 'dispatchChunkedBatch');
    $method->invoke(app(SyncGameData::class), $jobs, static function (): void {});
    Bus::assertBatched(fn ($batch): bool => $batch->jobs->count() === 101);
});

it('schedules installed item repair only from successful finalization', function (): void {
    $version = GameVersion::factory()->create(['is_default' => true]);
    Storage::disk('scunpacked')->put('ships/ship.json', '{}');
    Bus::fake();
    $this->artisan('game:sync-data', ['--game-version' => $version->code])->assertSuccessful();
    Bus::assertNotDispatched(SyncVehicleInstalledItems::class);
    $batch = Bus::batched(fn ($batch): bool => true)->first();
    expect($batch->thenCallbacks())->toHaveCount(1);
    Artisan::shouldReceive('call')->andReturn(0);
    $completed = Mockery::mock(Batch::class);
    $completed->shouldReceive('cancelled')->andReturnFalse();
    ($batch->thenCallbacks()[0])($completed);
    Bus::assertDispatched(SyncVehicleInstalledItems::class);
});

it('does not run the success callback when a batch job fails', function (): void {
    Queue::fake();
    $method = new ReflectionMethod(SyncGameData::class, 'dispatchChunkedBatch');
    $method->invoke(app(SyncGameData::class), collect(array_fill(0, 1001, new ImportVehicleData(1, 'ships/test.json'))), static function (): void {
        SyncVehicleInstalledItems::dispatch(1);
    });
    $id = Queue::pushed(AddBatchJobs::class)->first()->batchId;
    $batch = app(BatchRepository::class)->find($id);
    $batch->recordFailedJob('failed-loader', new RuntimeException('failed'));
    $batch->recordSuccessfulJob('successful-loader');
    expect($batch->fresh()->cancelled())->toBeTrue();
    Queue::assertNotPushed(SyncVehicleInstalledItems::class);
});

it('does not finalize a cancelled batch when pending jobs reach zero', function (): void {
    $version = GameVersion::factory()->create(['is_default' => true]);
    Storage::disk('scunpacked')->put('ships/ship.json', '{}');
    Bus::fake();
    $this->artisan('game:sync-data', ['--game-version' => $version->code])->assertSuccessful();
    $batch = Bus::batched(fn ($batch): bool => true)->first();
    $cancelled = Mockery::mock(Batch::class);
    $cancelled->shouldReceive('cancelled')->andReturnTrue();
    ($batch->thenCallbacks()[0])($cancelled);
    Bus::assertNotDispatched(SyncVehicleInstalledItems::class);
});

it('repairs installed items after real database queue loaders finish out of order', function (): void {
    config()->set('queue.default', 'database');
    config()->set('queue.connections.database.queue', 'j3-'.Str::uuid());
    $version = GameVersion::factory()->create(['is_default' => true]);
    $itemUuid = fake()->uuid();
    $manufacturerUuid = fake()->uuid();
    Storage::disk('scunpacked')->put('manufacturers.json', json_encode([['Reference' => $manufacturerUuid, 'Name' => 'Queue Manufacturer', 'Code' => 'QUE']], JSON_THROW_ON_ERROR));
    Storage::disk('scunpacked')->put('items/item.json', json_encode(['Item' => ['reference' => $itemUuid, 'name' => 'Queue Item', 'className' => 'QUEUE_ITEM']], JSON_THROW_ON_ERROR));
    Storage::disk('scunpacked')->put('ships/ship.json', json_encode(['UUID' => fake()->uuid(), 'ClassName' => 'QUEUE_SHIP', 'Name' => 'Queue Ship', 'Manufacturer' => ['UUID' => $manufacturerUuid], 'Loadout' => [['UUID' => $itemUuid]]], JSON_THROW_ON_ERROR));

    $this->artisan('game:sync-data', ['--game-version' => $version->code])->assertSuccessful();
    $batchId = queuedBatchId(config('queue.connections.database.queue'));
    $batch = app(BatchRepository::class)->find($batchId);
    expect($batch->totalJobs)->toBe(1)->and($batch->options['then'])->toHaveCount(1);
    $worker = app('queue.worker');
    $queue = config('queue.connections.database.queue');
    $run = static fn () => $worker->runNextJob('database', $queue, new WorkerOptions);
    $run();
    expect($batch->fresh()->pendingJobs)->toBe(2);
    $itemJob = DB::table('jobs')->where('queue', $queue)->where('payload', 'like', '%ImportItemData%')->value('id');
    expect($itemJob)->not->toBeNull();
    DB::table('jobs')->where('id', $itemJob)->update(['available_at' => now()->addHour()->timestamp]);
    $run();
    $vehicle = VehicleData::where('game_version_id', $version->id)->firstOrFail();
    expect($vehicle->installedItems()->count())->toBe(0)->and($batch->fresh()->pendingJobs)->toBe(1);
    DB::table('jobs')->where('id', $itemJob)->update(['available_at' => now()->timestamp]);
    $run();
    expect($batch->fresh()->pendingJobs)->toBe(0);
    $repair = DB::table('jobs')->where('queue', $queue)->where('payload', 'like', '%SyncVehicleInstalledItems%')->value('id');
    expect($repair)->not->toBeNull();
    DB::table('jobs')->where('queue', $queue)->where('id', '<>', $repair)->update(['available_at' => now()->addHour()->timestamp]);
    $run();
    expect($vehicle->installedItems()->whereHas('item', fn ($query) => $query->where('uuid', $itemUuid))->count())->toBe(1);
});

it('does not finalize a cancelled real database queue batch', function (): void {
    config()->set('queue.default', 'database');
    config()->set('queue.connections.database.queue', 'j3-'.Str::uuid());
    $version = GameVersion::factory()->create(['is_default' => true]);
    Storage::disk('scunpacked')->put('ships/ship.json', '{}');
    $this->artisan('game:sync-data', ['--game-version' => $version->code])->assertSuccessful();
    $batch = app(BatchRepository::class)->find(queuedBatchId(config('queue.connections.database.queue')));
    $batch->cancel();
    app('queue.worker')->runNextJob('database', config('queue.connections.database.queue'), new WorkerOptions);
    expect($batch->fresh()->pendingJobs)->toBe(0)
        ->and(DB::table('jobs')->where('queue', config('queue.connections.database.queue'))->count())->toBe(0);
});

it('registers both real database queue loaders across the chunk boundary', function (): void {
    config()->set('queue.default', 'database');
    config()->set('queue.connections.database.queue', 'j3-'.Str::uuid());
    $jobs = collect(range(1, 1001))->map(fn (int $number) => new ImportVehicleData(1, "ships/{$number}.json"));
    $method = new ReflectionMethod(SyncGameData::class, 'dispatchChunkedBatch');
    $method->invoke(app(SyncGameData::class), $jobs, null);
    $batch = app(BatchRepository::class)->find(queuedBatchId(config('queue.connections.database.queue')));
    expect($batch->totalJobs)->toBe(2)->and($batch->pendingJobs)->toBe(2);
    $worker = app('queue.worker');
    $queue = config('queue.connections.database.queue');
    $worker->runNextJob('database', $queue, new WorkerOptions);
    expect($batch->fresh()->pendingJobs)->toBe(1001)->and($batch->fresh()->finished())->toBeFalse();
    $worker->runNextJob('database', $queue, new WorkerOptions);
    expect($batch->fresh()->pendingJobs)->toBe(1001)
        ->and(DB::table('jobs')->where('queue', $queue)->count())->toBe(1001);
});


it('registers 101 loaders in the real database queue', function (): void {
    expect(DB::connection()->getDriverName())->toBe('pgsql');
    config()->set('queue.default', 'database');
    $queue = 'j3-'.Str::uuid();
    config()->set('queue.connections.database.queue', $queue);
    $jobs = collect(range(1, 100001))->map(fn (int $number) => new ImportVehicleData(1, "ships/{$number}.json"));
    $method = new ReflectionMethod(SyncGameData::class, 'dispatchChunkedBatch');
    $method->invoke(app(SyncGameData::class), $jobs, null);
    $batch = app(BatchRepository::class)->find(queuedBatchId($queue));
    expect($batch->totalJobs)->toBe(101)->and($batch->pendingJobs)->toBe(101)
        ->and(DB::table('jobs')->where('queue', $queue)->count())->toBe(101);
});
