<?php

declare(strict_types=1);

namespace App\Jobs\Api\StarCitizen\Vehicle\Parser;

use App\Traits\Jobs\ShipMatrix\GetNewestShipMatrixFilenameTrait as GetNewestShipMatrixFilename;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

use function GuzzleHttp\json_decode;

/**
 * Class ParseShipsDownload
 */
class ParseShipMatrixDownload implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use GetNewestShipMatrixFilename;

    private $shipMatrixFileName;

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
            $vehicles = json_decode(Storage::disk('vehicles')->get($this->shipMatrixFileName));
        } catch (FileNotFoundException $e) {
            app('Log')::error(
                "File {$this->shipMatrixFileName} not found on Disk vehicles",
                [
                    'message' => $e->getMessage(),
                ]
            );

            return;
        } catch (InvalidArgumentException $e) {
            app('Log')::error(
                "File {$this->shipMatrixFileName} does not contain valid JSON",
                [
                    'message' => $e->getMessage(),
                ]
            );

            return;
        }

        collect($vehicles)->each(
            function ($vehicle) {
                dispatch(new ParseVehicle(new Collection($vehicle)));
            }
        );
    }
}
