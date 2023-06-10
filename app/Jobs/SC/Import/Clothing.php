<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use App\Services\Parser\SC\Labels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;

class Clothing implements ShouldQueue
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

        try {
            $parser = new \App\Services\Parser\SC\Clothing($this->filePath, $labels);
        } catch (FileNotFoundException | JsonException $e) {
            $this->fail($e);
            return;
        }

        $item = $parser->getData();

        try {
            $model = \App\Models\SC\Char\Clothing\Clothing::where('uuid', $item['uuid'])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return;
        }

        if (isset($item['resistances'])) {
            if (!empty($item['damage_reduction'])) {
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
    }
}
