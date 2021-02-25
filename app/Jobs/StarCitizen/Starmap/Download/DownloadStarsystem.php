<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Starmap\Download;

use App\Jobs\StarCitizen\AbstractRSIDownloadData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;

class DownloadStarsystem extends AbstractRSIDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const STARSYSTEM_ENDPOINT = '/api/starmap/star-systems/';
    private const STRUCTURE_CHECKLIST = ['data', 'resultset', 0, 'celestial_objects', 0];

    private string $systemCode;
    private array $systemData;
    private string $folder;
    private ?Collection $bootupData;

    private Response $response;

    /**
     * Create a new job instance.
     *
     * @param string          $systemCode
     * @param string          $folder
     * @param Collection|null $bootupData
     */
    public function __construct(string $systemCode, string $folder, Collection $bootupData = null)
    {
        $this->systemCode = $systemCode;
        $this->folder = $folder;
        $this->bootupData = $bootupData;
        $this->makeClient();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->downloadStarSystem();
        $this->checkStructure();
        $this->saveSystemToDisk();
    }

    /**
     * Downloads the star system and saves the response
     */
    private function downloadStarSystem(): void
    {
        try {
            $this->response = $this->makeClient()
                ->post(sprintf('%s%s', self::STARSYSTEM_ENDPOINT, $this->systemCode))
                ->throw();
        } catch (RequestException $e) {
            app('Log')::error(
                sprintf('Could not connect to RSI Starmap %s', $this->systemCode),
                [
                    'message' => $e->getMessage(),
                ]
            );

            $this->release(300);

            return;
        }
    }

    /**
     * Checks the downloaded structure
     */
    private function checkStructure(): void
    {
        try {
            $this->systemData = json_decode(
                $this->response->body(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            app('Log')::error(sprintf('Can\'t decode %s.', $this->systemCode));

            $this->fail($e);
        }

        if (!$this->checkDataStructureIsValid($this->systemData, static::STRUCTURE_CHECKLIST)) {
            $this->fail('Starsystem data can\'t be processed.');
        }
    }

    /**
     * Writes the system json to disk
     */
    private function saveSystemToDisk(): void
    {
        $system = $this->systemData['data']['resultset'][0];

        if ($this->bootupData !== null) {
            $system = $this->bootupData->merge($system);
        }

        try {
            Storage::disk(DownloadStarmap::STARSYSTEM_DISK)->put(
                sprintf('%s/%s_system.json', $this->folder, Str::slug($this->systemCode)),
                json_encode($system, JSON_THROW_ON_ERROR)
            );
        } catch (JsonException $e) {
            app('Log')::error(sprintf('Can\'t encode %s to json.', $this->systemCode));

            $this->fail($e);
        }
    }
}
