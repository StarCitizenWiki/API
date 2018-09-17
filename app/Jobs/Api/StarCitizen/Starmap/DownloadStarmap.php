<?php

namespace App\Jobs\Api\StarCitizen\Starmap;

use App\Jobs\AbstractBaseDownloadData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;
use InvalidArgumentException;
use App\Exceptions\InvalidDataException;
use GuzzleHttp\Exception\ConnectException;

/**
 * Class DownloadStarmap
 * @package App\Jobs\Api\StarCitizen\Starmap
 */
class DownloadStarmap extends AbstractBaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const STARSYSTEM_BOOTUP_ENDPOINT = "/api/starmap/bootup";
    const STARSYSTEM_ENDPOINT = '/api/starmap/star-systems/';
    public const STARMAP_BOOTUP_FILENAME = "bootup.json";
    private const STARSYSTEM_DISK = 'starsystem';

    // Requests for Celestial Subobject
    const CELESTIAL_SUBOBJECTS_REQUEST = ['PLANET'];
    // Add Type to Starmapdata
    const CELESTIAL_SUBOBJECTS_TYPE = ['LZ'];

    const OVERVIEWDATA_CHECKLIST = ['data', 'systems', 'resultset', 0];
    const CELESTIALOBJECTS_CHECKLIST = ['data', 'resultset', 0, 'celestial_objects', 0];
    const CELESTIALSUBOBJECTS_CHECKLIST = ['data', 'resultset', 0, 'children', 0];

    private $starsystems;

    private $starsystemsUpdated = 0;
    private $celestialObjectsUpdated = 0;

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
        app('Log')::info('Starting Starmap Download');

        $timestamp = now()->format("Y-m-d");

        if ($this->force || !Storage::disk(self::STARSYSTEM_DISK)->exists($timestamp)) {
            $this->initClient();
            $this->setBootup($timestamp);
            $this->setStarsystems($timestamp);
        }

//TODO Mail an api@startcitizen.wiki und ins log mit Anzahl wieviel System und Celestial Objects Updated

        app('Log')::info(
            "Starmap Download Job Finished (Starsystems updated:{$this->starsystemsUpdated} 
                         CelestialObjects updated:{$this->celestialObjectsUpdated})"
        );
    }

    private function setBootup($timestamp) : void
    {
        try {
            $response = $this->client->post(self::STARSYSTEM_BOOTUP_ENDPOINT);
        } catch (ConnectException $e) {
            app('Log')::critical(
                'Could not connect to RSI Starmap Bootup',
                [
                    'message' => $e->getMessage(),
                ]
            );

            return;
        }

        $overviewData = json_decode($response->getBody()->getContents(), true);
        $this->checkAndSetStarsystems($overviewData);

        Storage::disk(self::STARSYSTEM_DISK)->put($timestamp . "/" . self::STARMAP_BOOTUP_FILENAME,
                                                  json_encode($overviewData));
    }

    private function checkAndSetStarsystems($overviewData) {
        try {
            if ($this->checkIfDataCanBeProcessed($overviewData, static::OVERVIEWDATA_CHECKLIST)) {
                $this->starsystems = $overviewData['data']['systems']['resultset'];
            } else {
                throw new InvalidDataException('Can not read Star-Systems from RSI');
            }
        } catch (InvalidArgumentException $e) {
            app('Log')::error(
                'Starmap Bootup data is not valid json',
                [
                    'message' => $e->getMessage(),
                ]
            );

            return;
        } catch (InvalidDataException $e) {
            app('Log')::error($e->getMessage());

            return;
        }
    }

    private function setStarsystems($timestamp) : void
    {
        foreach ($this->starsystems as $system) {
            $system = $system['code'];

            try {
                $response = $this->client->post(self::STARSYSTEM_ENDPOINT . $system);
            } catch (ConnectException $e) {
                app('Log')::critical(
                    'Could not connect to RSI Starmap ' . $system,
                    [
                        'message' => $e->getMessage(),
                    ]
                );

                return;
            }

            try {
                $starsystemData = json_decode($response->getBody()->getContents(), true);
                if ($this->checkIfDataCanBeProcessed($starsystemData, static::CELESTIALOBJECTS_CHECKLIST)) {
                    $celestialObjects = $starsystemData['data']['resultset'][0]['celestial_objects'];
                }
            } catch (InvalidArgumentException $e) {
                app('Log')::error(
                    'Starmap Bootup data is not valid json',
                    [
                        'message' => $e->getMessage(),
                    ]
                );

                return;
            } catch (InvalidDataException $e) {
                app('Log')::error($e->getMessage());

                return;
            }

            Storage::disk(self::STARSYSTEM_DISK)->put($timestamp . "/" . $system."_system.json", json_encode($celestialObjects));
        }
    }
}