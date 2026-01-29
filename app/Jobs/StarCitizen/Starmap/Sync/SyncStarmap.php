<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Starmap\Sync;

use App\Jobs\StarCitizen\Starmap\Download\DownloadStarsystem;
use App\Jobs\StarCitizen\Starmap\Import\ImportJumppoint;
use App\Jobs\StarCitizen\Starmap\Import\ImportStarsystem;
use App\Services\RsiDownloadClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JsonException;

class SyncStarmap implements ShouldQueue
{
    use Queueable;

    /**
     * Disk to save star map data to.
     */
    public const STARSYSTEM_DISK = 'starmap';

    /**
     * Bootup data filename.
     */
    public const STARMAP_BOOTUP_FILENAME = 'bootup.json';

    /**
     * Bootup endpoint.
     */
    private const STARSYSTEM_BOOTUP_ENDPOINT = '/api/starmap/bootup';

    public int $timeout = 120;

    private Collection $systems;

    private Collection $tunnels;

    private Response $response;

    private string $timestamp;

    public function __construct()
    {
        $this->timestamp = now()->format('Y-m-d');
    }

    /**
     * Execute job.
     */
    public function handle(RsiDownloadClient $client): void
    {
        $bootupData = $this->loadBootupFromDisk();

        if ($bootupData === null) {
            $this->downloadBootup($client);

            if (! isset($this->response)) {
                return;
            }

            $bootupData = $this->decodeBootup();

            if ($bootupData === null) {
                return;
            }

            $this->writeBootupDataToDisk($bootupData);
        }

        $this->dispatchJumppointJobs();
        $this->dispatchStarsystemJobs();
    }

    /**
     * Download bootup data.
     */
    private function downloadBootup(RsiDownloadClient $client): void
    {
        $response = $client->forRsi()->post(self::STARSYSTEM_BOOTUP_ENDPOINT);

        if ($response->serverError()) {
            Log::error('Could not connect to RSI Starmap Bootup', [
                'status' => $response->status(),
            ]);

            $this->release(300);

            return;
        }

        if ($response->clientError()) {
            Log::warning('Starmap bootup request failed', [
                'status' => $response->status(),
            ]);

            return;
        }

        $this->response = $response;
    }

    private function decodeBootup(): ?array
    {
        try {
            $bootupData = json_decode(
                $this->response->body(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            $this->fail($e);

            return null;
        }

        return $this->parseBootupData($bootupData);
    }

    private function parseBootupData(array $bootupData): ?array
    {
        if (Arr::get($bootupData, 'data.systems.resultset.0') === null) {
            $this->fail('Can not read Star-Systems from RSI');

            return null;
        }

        if (Arr::get($bootupData, 'data.tunnels.resultset.0') === null) {
            $this->fail('Bootup tunnel data not valid.');

            return null;
        }

        $this->systems = collect($bootupData['data']['systems']['resultset']);
        $this->tunnels = collect($bootupData['data']['tunnels']['resultset']);

        return $bootupData;
    }

    private function loadBootupFromDisk(): ?array
    {
        $bootupPath = sprintf('%s/%s', $this->timestamp, self::STARMAP_BOOTUP_FILENAME);

        if (! Storage::disk(self::STARSYSTEM_DISK)->exists($bootupPath)) {
            return null;
        }

        $payload = Storage::disk(self::STARSYSTEM_DISK)->get($bootupPath);

        try {
            $bootupData = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->fail($e);

            return null;
        }

        return $this->parseBootupData($bootupData);
    }

    private function writeBootupDataToDisk(array $bootupData): void
    {
        Storage::disk(self::STARSYSTEM_DISK)->makeDirectory($this->timestamp);

        try {
            Storage::disk(self::STARSYSTEM_DISK)->put(
                sprintf('%s/%s', $this->timestamp, self::STARMAP_BOOTUP_FILENAME),
                json_encode($bootupData, JSON_THROW_ON_ERROR)
            );
        } catch (JsonException $e) {
            $this->fail($e);
        }
    }

    private function dispatchJumppointJobs(): void
    {
        $this->tunnels->each(
            function (array $tunnel): void {
                ImportJumppoint::dispatch($tunnel);
            }
        );
    }

    /**
     * Download each star system.
     */
    private function dispatchStarsystemJobs(): void
    {
        $importJobs = [];
        $downloadJobs = [];

        foreach ($this->systems as $system) {
            $systemCode = $system['code'];

            if ($this->hasStarsystemData($systemCode)) {
                $data = $this->loadStarsystemDataFromDisk($systemCode);

                if ($data !== null) {
                    $importJobs[] = new ImportStarsystem($data);
                }
            } else {
                $downloadJobs[] = new DownloadStarsystem($systemCode, $this->timestamp, new Collection($system));
            }
        }

        if (count($importJobs) > 0) {
            try {
                Bus::batch($importJobs)->dispatch();
            } catch (\Throwable $e) {
            }
        }

        if (count($downloadJobs) > 0) {
            try {
                Bus::batch($downloadJobs)->dispatch();
            } catch (\Throwable $e) {
            }
        }
    }

    private function hasStarsystemData(string $systemCode): bool
    {
        $path = sprintf('%s/%s_system.json', $this->timestamp, strtolower($systemCode));

        return Storage::disk(self::STARSYSTEM_DISK)->exists($path);
    }

    private function loadStarsystemDataFromDisk(string $systemCode): ?array
    {
        $path = sprintf('%s/%s_system.json', $this->timestamp, strtolower($systemCode));

        if (! Storage::disk(self::STARSYSTEM_DISK)->exists($path)) {
            return null;
        }

        $content = Storage::disk(self::STARSYSTEM_DISK)->get($path);

        if (empty($content)) {
            return null;
        }

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::error('Failed to decode starsystem JSON from disk', [
                'system_code' => $systemCode,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        return $data;
    }
}
