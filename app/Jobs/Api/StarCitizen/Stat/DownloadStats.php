<?php declare(strict_types = 1);

namespace App\Jobs\Api\StarCitizen\Stat;

use App\Exceptions\InvalidDataException;
use App\Jobs\AbstractBaseDownloadData;
use App\Models\Api\StarCitizen\Stat;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\Psr7\str;
use App\Jobs\Api\StarCitizen\AbstractRSIDownloadData as RSIDownloadData;

/**
 * Class DownloadStats
 */
class DownloadStats extends RSIDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const STATS_ENDPOINT = '/api/stats/getCrowdfundStats';
    private const STATS_DISK = 'stats';

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
        app('Log')::info('Starting Stats Download Job.');

        $timestamp = now()->format("Y-m-d");
        $fileName = "stats_{$timestamp}.json";

        if ($this->force || !Storage::disk(self::STATS_DISK)->exists($fileName)) {
            $this->initClient();

            try {
                $response = $this->client->post(
                    self::STATS_ENDPOINT,
                    [
                        'json' => [
                            'fans' => true,
                            'fleet' => true,
                            'funds' => true,
                        ],
                    ]
                );
            } catch (ConnectException $e) {
                app('Log')::critical(
                    'Could not connect to RSI Stats Endpoint',
                    [
                        'message' => $e->getMessage(),
                    ]
                );

                return;
            }

            try {
                $response = $this->parseResponseBody((string) $response->getBody());
            } catch (InvalidArgumentException $e) {
                app('Log')::error(
                    'Stats data is not valid json',
                    [
                        'message' => $e->getMessage(),
                    ]
                );

                return;
            } catch (InvalidDataException $e) {
                app('Log')::error($e->getMessage());

                return;
            }

            Storage::disk(self::STATS_DISK)->put($fileName, json_encode($response->data));
        }

        app('Log')::info('Stat Download finished, dispatching Parsing Job');
        dispatch(new ParseStat($fileName));
    }
}
