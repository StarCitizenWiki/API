<?php

declare(strict_types=1);

use App\Console\Commands\Game\SyncGameData;
use App\Models\Game\GameVersion;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('dispatches all item import jobs in a single batch', function () {
    Bus::fake();
    Storage::fake('scunpacked');

    $fileCount = 2500;
    $expectedLoaderCount = (int) ceil($fileCount / 1000);

    for ($index = 0; $index < $fileCount; $index++) {
        Storage::disk('scunpacked')->put("items/{$index}.json", '{}');
    }

    $gameVersion = GameVersion::factory()->create();

    $command = new SyncGameData;
    $method = (new ReflectionClass($command))->getMethod('dispatchItemImports');
    $method->setAccessible(true);
    $method->invoke($command, $gameVersion, true);

    Bus::assertBatchCount(1);
    Bus::assertBatched(function (PendingBatch $batch) use ($expectedLoaderCount): bool {
        return $batch->jobs->every(fn ($job) => $job instanceof \App\Jobs\Game\AddBatchJobs)
            && $batch->jobs->count() === $expectedLoaderCount;
    });
});

it('dispatches compute base ids after all imports', function () {
    Bus::fake();
    Storage::fake('scunpacked');

    $fileCount = 100;
    $expectedLoaderCount = (int) ceil($fileCount / 1000);

    for ($index = 0; $index < $fileCount; $index++) {
        Storage::disk('scunpacked')->put("items/{$index}.json", '{}');
    }

    $gameVersion = GameVersion::factory()->create();

    $command = new SyncGameData;
    $method = (new ReflectionClass($command))->getMethod('dispatchItemImports');
    $method->setAccessible(true);
    $method->invoke($command, $gameVersion, false);

    Bus::assertBatched(function (PendingBatch $batch) use ($expectedLoaderCount): bool {
        $callbacks = $batch->thenCallbacks();

        return count($callbacks) === 1
            && $batch->jobs->count() === $expectedLoaderCount
            && $batch->jobs->every(fn ($job) => $job instanceof \App\Jobs\Game\AddBatchJobs);
    });
});
