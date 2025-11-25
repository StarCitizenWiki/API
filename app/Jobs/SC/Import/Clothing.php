<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;

class Clothing extends AbstractItemCreationJob
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
            $parser = new \App\Services\Parser\SC\Clothing($this->filePath, $this->labels);
        } catch (FileNotFoundException|JsonException $e) {
            $this->fail($e);

            return;
        }

        $item = $parser->getData();

        try {
            /** @var \App\Models\SC\Char\Clothing\Clothing $model */
            $model = \App\Models\SC\Char\Clothing\Clothing::query()->withoutGlobalScopes()->where('uuid', $item['uuid'])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return;
        }

        if (isset($item['resistances'])) {
            if (! empty($item['damage_reduction'])) {
                $model->resistances()->updateOrCreate([
                    'type' => 'damage_reduction',
                ], [
                    'multiplier' => str_replace('%', '', $item['damage_reduction']) / 100,
                ]);
            }

            foreach ($item['resistances'] as $type => $resistance) {
                $model->resistances()->updateOrCreate([
                    'type' => $type,
                ], [
                    'multiplier' => $resistance['multiplier'] ?? null,
                    'threshold' => $resistance['threshold'] ?? null,
                ]);
            }
        }

        if (!empty($item['radiation_resistance'])) {
            $model->radiationResistance()->updateOrCreate([
               'item_uuid' => $item['uuid'],
            ], [
                'maximum_radiation_capacity' => $item['radiation_resistance']['maximum_radiation_capacity'],
                'radiation_dissipation_rate' => $item['radiation_resistance']['radiation_dissipation_rate'],
            ]);
        }
    }
}
