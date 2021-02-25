<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Stat;

use App\Exceptions\InvalidDataException;
use App\Jobs\StarCitizen\AbstractRSIDownloadData as RSIDownloadData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use JsonException;

/**
 * Class DownloadStats
 */
class DownloadStats extends RSIDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const STATS_ENDPOINT = '/api/stats/getCrowdfundStats';
    private const STATS_DISK = 'stats';

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
     * @throws JsonException
     */
    public function handle(): void
    {
        app('Log')::info('Starting Stats Download Job.');

        $path = sprintf('%d/stats_%s.json', now()->year, now()->format('Y-m-d'));

        if (!$this->force && Storage::disk(self::STATS_DISK)->exists($path)) {
            return;
        }

        try {
            $response = $this->makeClient()
                ->asForm()
                ->post(
                    self::STATS_ENDPOINT,
                    [
                        'fans' => true,
                        'fleet' => true,
                        'funds' => true,
                    ]
                )->throw();
        } catch (RequestException $e) {
            app('Log')::critical(
                'Could not connect to RSI Stats Endpoint',
                [
                    'message' => $e->getMessage(),
                ]
            );

            $this->fail($e);

            return;
        }

        $this->saveStats($response, $path);

        app('Log')::info('Stat Download finished');
    }

    private function saveStats(Response $response, string $path): void
    {
        try {
            $response = $this->parseResponseBody($response->body());
        } catch (InvalidDataException $e) {
            app('Log')::error(
                'Stats data is not valid json',
                [
                    'message' => $e->getMessage(),
                ]
            );

            $this->fail($e);

            return;
        }

        Storage::disk(self::STATS_DISK)->put(
            $path,
            json_encode($response->data, JSON_THROW_ON_ERROR)
        );
    }
}
