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

    $fileCount = 2500; // More than old BATCH_SIZE of 1000

    for ($index = 0; $index < $fileCount; $index++) {
        Storage::disk('scunpacked')->put("items/{$index}.json", '{}');
    }

    $gameVersion = GameVersion::factory()->create();

    $command = new SyncGameData;
    $method = (new ReflectionClass($command))->getMethod('dispatchItemImports');
    $method->setAccessible(true);
    $method->invoke($command, $gameVersion, true);

    Bus::assertBatchCount(1);
    Bus::assertBatched(function (PendingBatch $batch) use ($fileCount): bool {
        return $batch->jobs->count() === $fileCount;
    });
});

it('dispatches compute base ids after all imports', function () {
    Bus::fake();
    Storage::fake('scunpacked');

    for ($index = 0; $index < 100; $index++) {
        Storage::disk('scunpacked')->put("items/{$index}.json", '{}');
    }

    $gameVersion = GameVersion::factory()->create();

    $command = new SyncGameData;
    $method = (new ReflectionClass($command))->getMethod('dispatchItemImports');
    $method->setAccessible(true);
    $method->invoke($command, $gameVersion, false); // Don't skip compute

    Bus::assertBatched(function (PendingBatch $batch): bool {
        $callbacks = $batch->thenCallbacks();

        return count($callbacks) === 1 && $batch->jobs->count() === 100;
    });
});
