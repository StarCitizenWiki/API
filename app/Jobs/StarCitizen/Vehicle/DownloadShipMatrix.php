<?php declare(strict_types = 1);

namespace App\Jobs\StarCitizen\Vehicle;

use App\Jobs\AbstractBaseDownloadData;
use App\Jobs\StarCitizen\Vehicle\Parser\ParseShipMatrixDownload;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

/**
 * Class DownloadShips
 */
class DownloadShipMatrix extends AbstractBaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const SHIPS_ENDPOINT = '/ship-matrix/index';
    private const VEHICLES_DISK = 'vehicles';

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('Log')::info('Starting Ship-Matrix Download');

        $timestamp = now()->format("Y-m-d");
        $fileName = "shipmatrix_{$timestamp}.json";

        if (!Storage::disk(self::VEHICLES_DISK)->exists($fileName)) {
            $this->initClient();

            try {
                $response = $this->client->get(self::SHIPS_ENDPOINT);
            } catch (ConnectException $e) {
                app('Log')::critical('Could not connect to RSI Ship Matrix');
                dump($e->getMessage());

                return;
            }

            try {
                $response = json_decode($response->getBody()->getContents(), true);
            } catch (InvalidArgumentException $e) {
                app('Log')::error('Ship Matrix data is not valid json');

                return;
            }

            if (!isset($response['success']) || $response['success'] !== 1) {
                app('Log')::error('Ship Matrix data is not valid (success != 1)');

                return;
            }

            Storage::disk(self::VEHICLES_DISK)->put($fileName, json_encode($response['data']));
        }

        dispatch(new ParseShipMatrixDownload($fileName));
    }
}
