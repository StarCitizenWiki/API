<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Vehicle;

use App\Events\StarCitizen\ShipMatrix\ShipMatrixStructureChanged;
use App\Traits\Jobs\ShipMatrix\GetNewestShipMatrixFilenameTrait as GetNewestShipMatrixFilename;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use JsonException;
use RuntimeException;

/**
 * Checks if the Ship Matrix structure has changed based on comparing the Aurora ES against a ground truth structure
 */
class CheckShipMatrixStructure implements ShouldQueue
{
    use Dispatchable;
    use GetNewestShipMatrixFilename;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private ?string $shipMatrix = null;

    private $groundTruth;

    /**
     * Execute the job.
     *
     * @throws JsonException
     */
    public function handle(): void
    {
        try {
            $this->shipMatrix = $this->getNewestShipMatrixFilename();
            $this->groundTruth = File::get(storage_path('framework/testing/shipmatrix/aurora_es.json'));

            $this->groundTruth = collect(json_decode($this->groundTruth, true, 512, JSON_THROW_ON_ERROR));
        } catch (FileNotFoundException|RuntimeException|JsonException $e) {
            $this->fail($e);
        }

        $vehicles = json_decode(Storage::disk('vehicles')->get($this->shipMatrix), true, 512, JSON_THROW_ON_ERROR);

        $diff = $this->groundTruth->diffKeys($vehicles[0]);

        if ($diff->count() !== 0) {
            $keys = $diff->keys();

            app('Log')::error('Ship Matrix structure changed, aborting job. Missing keys:', $keys->toArray());
            ShipMatrixStructureChanged::dispatch();

            $this->fail('Ship Matrix structure changed. Missing keys: '.$keys->implode(', '));
        }
    }
}
