<?php declare(strict_types = 1);

namespace App\Jobs\Api\StarCitizen\Vehicle\Parser;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
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
            $this->setShipMatrixFileName();
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
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

    /**
     * Tries to auto generate the Shipmatrix Filename to use
     */
    private function setShipMatrixFileName()
    {
        $newestShipMatrixDir = Arr::last(Storage::disk('vehicles')->directories());

        if (null === $newestShipMatrixDir) {
            $this->fail(new InvalidArgumentException('No Shipmatrix directories found'));
        } else {
            $file = Arr::last(Storage::disk('vehicles')->files($newestShipMatrixDir));

            if (null !== $file && Str::contains($file, 'shipmatrix')) {
                $this->shipMatrixFileName = $file;
            } else {
                app('Log')::error('No Shipmatrix File on Disk \'vehicles\' found');
                $this->fail();
            }
        }
    }
}
