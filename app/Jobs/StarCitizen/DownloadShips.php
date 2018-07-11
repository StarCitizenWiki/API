<?php declare(strict_types = 1);

namespace App\Jobs\StarCitizen;

use App\Exceptions\InvalidDataException;
use App\Jobs\AbstractBaseDownloadData;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use function GuzzleHttp\json_decode;

/**
 * Class DownloadShips
 */
class DownloadShips extends AbstractBaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const SHIPS_ENDPOINT = '/ship-matrix/index';

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->initClient();
        try {
            $response = $this->client->get(self::SHIPS_ENDPOINT);
        } catch (ConnectException $e) {
            app('Log')::critical('Could not connect to RSI Ship Matrix');

            return;
        }

        try {
            $response = json_decode($response->getBody()->getContents(), true);
        } catch (InvalidDataException $e) {
            app('Log')::error('Ship Matrix data is not valid json');

            return;
        }

        if (!isset($response['success']) || $response['success'] !== 1) {
            app('Log')::error('Ship Matrix data is not valid (success != 1)');

            return;
        }

        $this->processShips($response);
    }

    private function processShips(array $ships)
    {
        foreach ($ships as $ship) {

        }
    }
}
