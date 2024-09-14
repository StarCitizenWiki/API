<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;

class MiningLaser extends AbstractItemCreationJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->loadLabels();
        try {
            $parser = new \App\Services\Parser\SC\MiningLaser($this->filePath, $this->labels);
        } catch (FileNotFoundException|JsonException $e) {
            $this->fail($e);

            return;
        }
        $item = $parser->getData();

        /** @var \App\Models\SC\ItemSpecification\MiningLaser $model */
        \App\Models\SC\ItemSpecification\MiningLaser::query()->withoutGlobalScopes()->updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'power_transfer' => $item['power_transfer'] ?? null,
            'optimal_range' => $item['optimal_range'] ?? null,
            'maximum_range' => $item['maximum_range'] ?? null,
            'extraction_throughput' => $item['extraction_throughput'] ?? null,
            'module_slots' => $item['module_slots'] ?? null,
        ]);
    }
}
