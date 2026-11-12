<?php

declare(strict_types=1);

use App\Jobs\Game\ImportItemPrices;
use App\Jobs\Game\ImportMissionData;
use App\Models\Game\GameVersion;
use Illuminate\Bus\BatchRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

it('logs a failed mission batch and skipped linking and counters', function (): void {
    Queue::fake();
    Storage::fake('scunpacked');
    $version = GameVersion::factory()->create();
    Storage::disk('scunpacked')->put('contracts/test.json', '{}');
    Log::shouldReceive('error')->once()->withArgs(fn ($message, $context) => str_contains($message, 'mission') && str_contains($message, 'linking') && isset($context['batch_id'], $context['exception']));
    $this->artisan('game:import-missions', ['version' => $version->code])->assertSuccessful();
    $job = Queue::pushed(ImportMissionData::class)->first();
    app(BatchRepository::class)->find($job->batchId)->recordFailedJob('failed', new RuntimeException('failed'));
});

it('logs a failed price batch and skipped enrichment', function (): void {
    Queue::fake();
    GameVersion::factory()->create(['is_default' => true]);
    Log::shouldReceive('error')->once()->withArgs(fn ($message, $context) => str_contains($message, 'price') && str_contains($message, 'enrichment') && isset($context['batch_id'], $context['exception']));
    $this->artisan('game:import-item-prices')->assertSuccessful();
    $job = Queue::pushed(ImportItemPrices::class)->first();
    app(BatchRepository::class)->find($job->batchId)->recordFailedJob('failed', new RuntimeException('failed'));
});
