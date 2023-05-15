<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use App\Models\SC\Vehicle\VehicleItem as VehicleItemModel;
use App\Services\Parser\StarCitizenUnpacked\Labels;
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
        $this->createVehicleItemModel($labels);
    }

    private function createMiningLaserModel(Collection $labels): void
    {
        try {
            $parser = new \App\Services\Parser\StarCitizenUnpacked\MiningLaser($this->filePath, $labels);
        } catch (FileNotFoundException | JsonException $e) {
            $this->fail($e);
            return;
        }
        $item = $parser->getData();

        /** @var \App\Models\SC\ItemSpecification\MiningLaser\MiningLaser $model */
        $model = \App\Models\SC\ItemSpecification\MiningLaser\MiningLaser::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'power_transfer' => $item['power_transfer'] ?? null,
            'optimal_range' => $item['optimal_range'] ?? null,
            'maximum_range' => $item['maximum_range'] ?? null,
            'extraction_throughput' => $item['extraction_throughput'] ?? null,
            'module_slots' => $item['module_slots'] ?? null,
        ]);

        if (!empty($item['description'])) {
            $model->translations()->updateOrCreate([
                'locale_code' => 'en_EN',
            ], [
                'translation' => $item['description'],
            ]);
        }

        collect($item['modifiers'])
            ->filter()
            ->each(function ($item, $key) use ($model) {
                if ($item === null) {
                    return;
                }
                $model->modifiers()->updateOrCreate([
                    'name' => $key,
                ], [
                    'modifier' => $item,
                ]);
            });
    }

    private function createVehicleItemModel(Collection $labels): void
    {
        try {
            $parser = new \App\Services\Parser\StarCitizenUnpacked\VehicleItems\VehicleItem($this->filePath, $labels);
        } catch (FileNotFoundException | JsonException $e) {
            $this->fail($e);
            return;
        }

        $item = $parser->getData();

        VehicleItemModel::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'grade' => $item['grade'],
            'class' => $item['class'],
            'type' => $item['type'],
        ]);
    }
}
