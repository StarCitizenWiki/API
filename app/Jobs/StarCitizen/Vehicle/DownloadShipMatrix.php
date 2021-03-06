<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Vehicle;

use App\Exceptions\InvalidDataException;
use App\Jobs\StarCitizen\AbstractRSIDownloadData as RSIDownloadData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Class DownloadShips
 */
class DownloadShipMatrix extends RSIDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const SHIPS_ENDPOINT = '/ship-matrix/index';
    private const VEHICLES_DISK = 'vehicles';

    private bool $force = false;

    /**
     * DownloadShipMatrix constructor.
     *
     * @param bool $force Set to true do force download even if file already exists
     */
    public function __construct($force = false)
    {
        $this->force = $force;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info('Starting Ship Matrix Download Job');

        if (!$this->force && Storage::disk(self::VEHICLES_DISK)->exists($this->getPath())) {
            return;
        }

        try {
            $response = $this->makeClient()->get(self::SHIPS_ENDPOINT)->throw();
        } catch (RequestException $e) {
            app('Log')::critical(
                'Could not connect to RSI Ship Matrix',
                [
                    'message' => $e->getMessage(),
                ]
            );

            $this->fail($e);

            return;
        }

        try {
            $response = $this->parseResponseBody($response->body());
        } catch (InvalidDataException $e) {
            app('Log')::error(
                'Ship Matrix data is not valid json',
                [
                    'message' => $e->getMessage(),
                ]
            );

            $this->fail($e);

            return;
        }

        // Exception will not happen
        $responseJsonData = json_encode($response->data, JSON_THROW_ON_ERROR);

        Storage::disk(self::VEHICLES_DISK)->put($this->getPath(), $responseJsonData);

        app('Log')::info('Ship Matrix Download finished');
    }

    /**
     * Generates the Shipmatrix Filename
     *
     * @return string
     */
    private function getPath(): string
    {
        $dirName = now()->format('Y-m-d');
        $fileTimeStamp = now()->format('Y-m-d_H-i');
        $filename = "shipmatrix_{$fileTimeStamp}.json";

        return "{$dirName}/{$filename}";
    }
}
