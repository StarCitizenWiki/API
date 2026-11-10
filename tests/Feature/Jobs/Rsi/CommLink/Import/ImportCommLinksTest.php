<?php

declare(strict_types=1);

use App\Jobs\Rsi\CommLink\Import\ImportCommLink;
use App\Jobs\Rsi\CommLink\Import\ImportCommLinks;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

it('batches one import per comm link directory and uses finally callbacks', function (): void {
    Storage::fake('comm_links');
    Bus::fake();

    Storage::disk('comm_links')->put('12663/2020-01-01_000000.html', '<html></html>');
    Storage::disk('comm_links')->put('12664/2020-01-02_000000.html', '<html></html>');

    (new ImportCommLinks(-1))->handle();

    Bus::assertBatched(function (PendingBatch $batch): bool {
        return $batch->jobs->count() === 2
            && $batch->jobs->first() instanceof ImportCommLink
            && $batch->finallyCallbacks() !== []
            && $batch->thenCallbacks() === [];
    });
});

it('does not dispatch a batch without comm link directories', function (): void {
    Storage::fake('comm_links');
    Bus::fake();

    (new ImportCommLinks(-1))->handle();

    Bus::assertNothingBatched();
});
