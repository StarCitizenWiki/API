<?php declare(strict_types = 1);

namespace App\Jobs\Api\StarCitizen\Vehicle;

use App\Exceptions\InvalidDataException;
use App\Jobs\Api\StarCitizen\AbstractRSIDownloadData as RSIDownloadData;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/**
 * Class DownloadShips
 */
class DownloadShipMatrix extends RSIDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const SHIPS_ENDPOINT = '/ship-matrix/index';
    private const VEHICLES_DISK = 'vehicles';

    private $force;

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
    public function handle()
    {
        app('Log')::info('Starting Ship Matrix Download Job');

        $this->initClient();

        try {
            $response = $this->client->get(self::SHIPS_ENDPOINT);
        } catch (ConnectException $e) {
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
            $response = $this->parseResponseBody((string) $response->getBody());
        } catch (InvalidArgumentException $e) {
            app('Log')::error(
                'Ship Matrix data is not valid json',
                [
                    'message' => $e->getMessage(),
                ]
            );

            return;
        } catch (InvalidDataException $e) {
            app('Log')::error($e->getMessage());

            return;
        }

        $responseJsonData = json_encode($response->data);

        Storage::disk(self::VEHICLES_DISK)->put($this->getFileName(), $responseJsonData);

        app('Log')::info('Ship Matrix Download finished');
    }

    /**
     * Generates the Shipmatrix Filename
     *
     * @return string
     */
    private function getFileName(): string
    {
        $dirName = now()->format("Y-m-d");
        $fileTimeStamp = now()->format("Y-m-d_H-i");
        $filename = "shipmatrix_{$fileTimeStamp}.json";

        return "{$dirName}/{$filename}";
    }
}
