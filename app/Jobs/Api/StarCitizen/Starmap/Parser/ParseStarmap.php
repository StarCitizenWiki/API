<?php

declare(strict_types=1);

namespace App\Jobs\Api\StarCitizen\Starmap\Parser;

use App\Jobs\Api\StarCitizen\Starmap\Download\DownloadStarmap;
use App\Traits\Jobs\CheckRsiDataStructureTrait as CheckRsiDataStructure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;

/**
 * Class ParseStarmapDownload
 */
class ParseStarmap implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use CheckRsiDataStructure;

    private const STARSYSTEM_DISK = 'starmap';

    /**
     * @var string|null
     */
    private ?string $timestamp;

    /**
     * @var string
     */
    private $starmapFolder;

    /**
     * ParseStarmapDownload constructor.
     *
     * @param null|string $timestamp
     */
    public function __construct(?string $timestamp = null)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if (null === $this->timestamp) {
            $this->starmapFolder = $this->getNewestStarmapFolder();
        } else {
            $this->starmapFolder = Storage::disk(self::STARSYSTEM_DISK)->path($this->timestamp);
        }

        app('Log')::info('Parsing Starmap Download', [$this->starmapFolder]);

        $this->dispatchStarsystemJobs();
        $this->dispatchJumppointJobs();
    }

    /**
     * @return string
     */
    private function getNewestStarmapFolder(): string
    {
        $diskPath = Storage::disk(self::STARSYSTEM_DISK)->path('');

        $folders = collect(scandir($diskPath, SCANDIR_SORT_DESCENDING))->reject(
            function ($folder) {
                return Str::startsWith($folder, '.');
            }
        )->toArray();

        if ($folders === false || empty($folders)) {
            throw new RuntimeException(sprintf('%s is not a directory.', $diskPath));
        }

        return Storage::disk(self::STARSYSTEM_DISK)->path(array_shift($folders));
    }

    private function dispatchStarsystemJobs(): void
    {
        $files = scandir($this->starmapFolder);

        if (empty($files)) {
            app('Log')::error('Starmap disk is empty');

            $this->fail('Starmap disk is empty');
        }

        collect($files)->filter(
            function (string $path) {
                return Str::contains($path, 'system');
            }
        )
            ->map(
                function (string $systemPath) {
                    try {
                        return File::get(sprintf('%s/%s', $this->starmapFolder, $systemPath));
                    } catch (FileNotFoundException $e) {
                        $this->fail($e);

                        return '';
                    }
                }
            )
            ->filter(
                function ($systemData) {
                    // Should not happen
                    return !empty($systemData) && $systemData !== '';
                }
            )
            ->map(
                function (string $systemData) {
                    return json_decode($systemData, true, 512, JSON_THROW_ON_ERROR);
                }
            )->each(
                function (array $system) {
                    ParseStarsystem::dispatch($system);
                }
            );
    }

    private function dispatchJumppointJobs(): void
    {
        try {
            $bootupData = File::get(sprintf('%s/%s', $this->starmapFolder, DownloadStarmap::STARMAP_BOOTUP_FILENAME));
        } catch (FileNotFoundException $e) {
            $this->fail($e);
        }

        try {
            $bootupData = json_decode($bootupData, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->fail($e);
        }

        if (!$this->checkDataStructureIsValid($bootupData, ['data', 'tunnels', 'resultset', 0])) {
            $this->fail('Bootup tunnel data not valid.');
        }

        collect($bootupData['data']['tunnels']['resultset'])->each(
            function ($tunnel) {
                ParseJumppoint::dispatch($tunnel);
            }
        );
    }
}
