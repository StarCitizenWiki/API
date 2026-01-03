<?php

declare(strict_types=1);

use App\Console\Commands\Game\SyncGameData;
use App\Models\Game\GameVersion;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('dispatches item import batches in chunks', function () {
    Bus::fake();
    Storage::fake('scunpacked');

    $batchSize = (new ReflectionClass(SyncGameData::class))->getConstant('BATCH_SIZE');
    $fileCount = $batchSize + 1;

    for ($index = 0; $index < $fileCount; $index++) {
        Storage::disk('scunpacked')->put("items/{$index}.json", '{}');
    }

    $gameVersion = GameVersion::factory()->create();

    $command = new SyncGameData;
    $method = (new ReflectionClass($command))->getMethod('dispatchItemImports');
    $method->setAccessible(true);
    $method->invoke($command, $gameVersion, true);

    Bus::assertBatchCount(1);
    Bus::assertBatched(function (PendingBatch $batch) use ($batchSize): bool {
        return $batch->jobs->count() === $batchSize;
    });
});
