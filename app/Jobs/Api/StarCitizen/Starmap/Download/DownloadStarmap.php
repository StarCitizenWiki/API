<?php

declare(strict_types=1);

namespace App\Jobs\Api\StarCitizen\Starmap\Download;

use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class DownloadStarmap
 */
class DownloadStarmap extends BaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Disk to save star map data to
     */
    public const STARSYSTEM_DISK = 'starmap';

    /**
     * Bootup data filename
     */
    public const STARMAP_BOOTUP_FILENAME = 'bootup.json';

    /**
     * Bootup endpoint
     */
    private const STARSYSTEM_BOOTUP_ENDPOINT = '/api/starmap/bootup';

    /**
     * Keys in order that define a valid bootup structure
     */
    private const BOOTUP_CHECKLIST = ['data', 'systems', 'resultset', 0];

    /**
     * Star systems defined in bootup
     *
     * @var Collection
     */
    private Collection $systems;

    /**
     * @var bool Force Download
     */
    private $force;

    /**
     * @var string
     */
    private $timestamp;

    /**
     * @var ResponseInterface Bootup response
     */
    private ResponseInterface $response;

    /**
     * Star Map Download
     *
     * @param bool $force Set to true do force download even if file already exists
     */
    public function __construct($force = false)
    {
        $this->force = $force;
        $this->timestamp = now()->format('Y-m-d');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info('Starting Starmap Download');

        if ($this->force || !Storage::disk(self::STARSYSTEM_DISK)->exists($this->timestamp)) {
            $this->initClient();
            $this->downloadBootup();
            $this->writeBootupDataToDisk();

            $this->dispatchStarSystemJobs();
        }
    }

    /**
     * Download the bootup data
     */
    private function downloadBootup(): void
    {
        try {
            $this->response = self::$client->post(self::STARSYSTEM_BOOTUP_ENDPOINT);
        } catch (ConnectException $e) {
            app('Log')::error(
                'Could not connect to RSI Starmap Bootup',
                [
                    'message' => $e->getMessage(),
                ]
            );

            $this->release(300);
        }
    }

    /**
     * Write bootup data to star systems disk
     */
    private function writeBootupDataToDisk(): void
    {
        try {
            $bootupData = json_decode(
                $this->response->getBody()->getContents(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            $this->fail($e);

            return;
        }

        $this->checkBootupStructure($bootupData);

        try {
            Storage::disk(self::STARSYSTEM_DISK)->put(
                sprintf('%s/%s', $this->timestamp, self::STARMAP_BOOTUP_FILENAME),
                json_encode($bootupData, JSON_THROW_ON_ERROR)
            );
        } catch (JsonException $e) {
            $this->fail($e);
        }
    }

    /**
     * @param array $bootupData
     */
    private function checkBootupStructure(array $bootupData): void
    {
        if (!$this->checkDataStructureIsValid($bootupData, static::BOOTUP_CHECKLIST)) {
            app('Log')::error('Can not read Star-Systems from RSI');

            $this->fail('Can not read Star-Systems from RSI');

            return;
        }

        $this->systems = collect($bootupData['data']['systems']['resultset']);
    }

    /**
     * Download each star system
     */
    private function dispatchStarSystemJobs(): void
    {
        $this->systems
            ->each(
                function (array $system) {
                    DownloadStarsystem::dispatch($system['code'], $this->timestamp, new Collection($system));
                }
            );
    }
}
