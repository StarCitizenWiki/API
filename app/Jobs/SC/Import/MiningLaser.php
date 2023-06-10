<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use App\Services\Parser\SC\Labels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use JsonException;

class MiningLaser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $labels = (new Labels())->getData();
        $this->createMiningLaserModel($labels);
    }

    private function createMiningLaserModel(Collection $labels): void
    {
        try {
            $parser = new \App\Services\Parser\SC\MiningLaser($this->filePath, $labels);
        } catch (FileNotFoundException | JsonException $e) {
            $this->fail($e);
            return;
        }
        $item = $parser->getData();

        /** @var \App\Models\SC\ItemSpecification\MiningLaser $model */
        \App\Models\SC\ItemSpecification\MiningLaser::updateOrCreate([
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
