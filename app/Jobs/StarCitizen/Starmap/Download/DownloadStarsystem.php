<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Starmap\Download;

use App\Jobs\StarCitizen\Starmap\Import\ImportStarsystem;
use App\Services\RsiDownloadClient;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;

class DownloadStarsystem implements ShouldQueue
{
    use Batchable;
    use Queueable;

    private const STARSYSTEM_ENDPOINT = '/api/starmap/star-systems/';

    private const STRUCTURE_CHECKLIST = ['data', 'resultset', 0, 'celestial_objects', 0];

    private const STARSYSTEM_DISK = 'starmap';

    public int $timeout = 120;

    private array $systemData;

    private Response $response;

    public function __construct(
        public readonly string $systemCode,
        public readonly string $folder,
        public readonly ?Collection $bootupData = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(RsiDownloadClient $client): void
    {
        $this->downloadStarSystem($client);

        if (! isset($this->response)) {
            return;
        }

        if (! $this->checkStructure()) {
            return;
        }

        $system = $this->buildSystemPayload();

        $this->saveSystemToDisk($system);

        ImportStarsystem::dispatch($system);
    }

    /**
     * Downloads the star system and saves the response.
     */
    private function downloadStarSystem(RsiDownloadClient $client): void
    {
        $response = $client->forRsi()
            ->post(sprintf('%s%s', self::STARSYSTEM_ENDPOINT, $this->systemCode));

        if ($response->serverError()) {
            Log::error(sprintf('Could not connect to RSI Starmap %s', $this->systemCode), [
                'status' => $response->status(),
            ]);

            $this->release(300);

            return;
        }

        if ($response->clientError()) {
            Log::warning(sprintf('Starmap request failed for %s', $this->systemCode), [
                'status' => $response->status(),
            ]);

            return;
        }

        $this->response = $response;
    }

    /**
     * Checks the downloaded structure.
     */
    private function checkStructure(): bool
    {
        try {
            $this->systemData = json_decode(
                $this->response->body(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            Log::error(sprintf('Can\'t decode %s.', $this->systemCode));

            $this->fail($e);

            return false;
        }

        if (! $this->validateDataStructure($this->systemData, self::STRUCTURE_CHECKLIST)) {
            $this->fail('Starsystem data can\'t be processed.');

            return false;
        }

        return true;
    }

    /**
     * Validates data structure.
     */
    private function validateDataStructure(array $data, array $keys): bool
    {
        if (! isset($data['success']) || $data['success'] !== 1) {
            return false;
        }

        foreach ($keys as $key) {
            if (! array_key_exists($key, $data)) {
                return false;
            }
            $data = $data[$key];
        }

        return true;
    }

    private function buildSystemPayload(): array
    {
        $system = $this->systemData['data']['resultset'][0];

        if ($this->bootupData !== null) {
            $system = $this->bootupData->merge($system)->toArray();
        }

        return $system;
    }

    /**
     * Writes the system json to disk.
     */
    private function saveSystemToDisk(array $system): void
    {
        try {
            Storage::disk(self::STARSYSTEM_DISK)->put(
                sprintf('%s/%s_system.json', $this->folder, Str::slug($this->systemCode)),
                json_encode($system, JSON_THROW_ON_ERROR)
            );
        } catch (JsonException $e) {
            Log::error(sprintf('Can\'t encode %s to json.', $this->systemCode));

            $this->fail($e);
        }
    }
}
