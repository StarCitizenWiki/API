<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Vehicle\Import;

use App\Traits\Jobs\ShipMatrix\GetNewestShipMatrixFilenameTrait as GetNewestShipMatrixFilename;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use JsonException;
use RuntimeException;

/**
 * Class ParseShipsDownload
 */
class ImportShipMatrix implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use GetNewestShipMatrixFilename;

    private ?string $shipMatrixFileName = null;

    /**
     * Create a new job instance.
     *
     * @param null|string $shipMatrixFileName
     */
    public function __construct(?string $shipMatrixFileName = null)
    {
        if (null !== $shipMatrixFileName) {
            $this->shipMatrixFileName = $shipMatrixFileName;
        } else {
            try {
                $this->shipMatrixFileName = $this->getNewestShipMatrixFilename();
            } catch (RuntimeException $e) {
                $this->fail($e);
            }
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info('Parsing Ship Matrix Download');

        try {
            $content = Storage::disk('vehicles')->get($this->shipMatrixFileName ?? 'HowCanThisBeNull??');
            if ($content === null) {
                throw new FileNotFoundException();
            }

            $vehicles = json_decode(
                $content,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (FileNotFoundException $e) {
            app('Log')::error(
                "File {$this->shipMatrixFileName} not found on Disk vehicles",
                [
                    'message' => $e->getMessage(),
                ]
            );

            $this->fail($e);
            return;
        } catch (JsonException $e) {
            app('Log')::error(
                "File {$this->shipMatrixFileName} does not contain valid JSON",
                [
                    'message' => $e->getMessage(),
                ]
            );

            $this->delete();
            return;
        }

        collect($vehicles)->each(
            function ($vehicle) {
                dispatch(new ImportVehicle(new Collection($vehicle)));
            }
        );
    }
}
