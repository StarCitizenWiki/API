<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Stat;

use App\Services\RsiDownloadClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JsonException;
use RuntimeException;
use stdClass;

class DownloadStats implements ShouldQueue
{
    use Queueable;

    private const STATS_ENDPOINT = '/api/stats/getCrowdfundStats';

    private const STATS_DISK = 'stats';

    public int $timeout = 120;

    private string $statFileName;

    private int $year;

    public function __construct(
        ?string $statFileName = null,
        ?int $year = null,
        public readonly bool $force = false,
    ) {
        $this->statFileName = $statFileName ?? sprintf('stats_%s.json', now()->format('Y-m-d'));
        $this->year = $year ?? $this->inferYear($this->statFileName);
    }

    /**
     * Execute the job.
     */
    public function handle(RsiDownloadClient $client): void
    {
        Log::info('Starting Stats Download Job.');

        $path = sprintf('%d/%s', $this->year, $this->statFileName);

        if (! $this->force && Storage::disk(self::STATS_DISK)->exists($path)) {
            return;
        }

        $response = $client->forRsi()
            ->asForm()
            ->post(
                self::STATS_ENDPOINT,
                [
                    'fans' => true,
                    'fleet' => true,
                    'funds' => true,
                ]
            );

        if ($response->serverError()) {
            Log::critical('Could not connect to RSI Stats Endpoint', [
                'status' => $response->status(),
            ]);

            $this->release(300);

            return;
        }

        if ($response->clientError()) {
            Log::warning('Stats request failed with client error', [
                'status' => $response->status(),
            ]);

            return;
        }

        $this->saveStats($response, $path);

        Log::info('Stat Download finished');
    }

    private function validateRsiResponse(string $body): stdClass
    {
        try {
            $response = json_decode($body, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new RuntimeException('Invalid JSON response from RSI');
        }

        if (($response->success ?? 0) !== 1) {
            throw new RuntimeException(
                sprintf('RSI API returned failure. Expected success=1, got %d', $response->success ?? 0)
            );
        }

        return $response;
    }

    private function saveStats(Response $response, string $path): void
    {
        try {
            $validated = $this->validateRsiResponse($response->body());
        } catch (RuntimeException $e) {
            Log::error('Stats data is not valid', [
                'message' => $e->getMessage(),
            ]);

            $this->fail($e);

            return;
        }

        Storage::disk(self::STATS_DISK)->put(
            $path,
            json_encode($validated->data, JSON_THROW_ON_ERROR)
        );
    }

    private function inferYear(string $statFileName): int
    {
        if (preg_match('/^stats_(\d{4})-\d{2}-\d{2}\.json$/', $statFileName, $matches) === 1) {
            return (int) $matches[1];
        }

        return now()->year;
    }
}
