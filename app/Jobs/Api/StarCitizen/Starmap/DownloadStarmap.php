<?php declare(strict_types = 1);

namespace App\Jobs\Api\StarCitizen\Starmap;

use App\Exceptions\InvalidDataException;
use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use function GuzzleHttp\json_decode;

/**
 * Class DownloadStarmap
 */
class DownloadStarmap extends BaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const STARMAP_BOOTUP_FILENAME = 'bootup.json';

    private const STARSYSTEM_BOOTUP_ENDPOINT = '/api/starmap/bootup';
    private const STARSYSTEM_ENDPOINT = '/api/starmap/star-systems/';
    private const STARSYSTEM_DISK = 'starsystem';

    // Requests for Celestial Subobject
    const CELESTIAL_SUB_OBJECTS_REQUEST = ['PLANET'];
    // Add Type to Starmapdata
    const CELESTIAL_SUB_OBJECTS_TYPE = ['LZ'];

    const OVERVIEW_DATA_CHECKLIST = ['data', 'systems', 'resultset', 0];
    const CELESTIAL_OBJECTS_CHECKLIST = ['data', 'resultset', 0, 'celestial_objects', 0];
    const CELESTIAL_SUB_OBJECTS_CHECKLIST = ['data', 'resultset', 0, 'children', 0];

    /**
     * @var
     */
    private $starsystems;

    /**
     * @var int
     */
    private $starsystemsUpdated = 0;

    /**
     * @var int
     */
    private $celestialObjectsUpdated = 0;

    /**
     * @var bool Force Download
     */
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

    /**
     * @param string $timestamp
     */
    private function setBootup(string $timestamp): void
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

            $this->fail($e);

            return;
        }

        $overviewData = json_decode($response->getBody()->getContents(), true);
        $this->checkAndSetStarsystems($overviewData);

        Storage::disk(self::STARSYSTEM_DISK)->put(
            sprintf('%s/%s', $timestamp, self::STARMAP_BOOTUP_FILENAME),
            json_encode($overviewData)
        );
    }

    /**
     * @param array $overviewData
     */
    private function checkAndSetStarsystems(array $overviewData): void
    {
        try {
            if ($this->checkIfDataCanBeProcessed($overviewData, static::OVERVIEW_DATA_CHECKLIST)) {
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

    /**
     * @param string $timestamp
     */
    private function setStarsystems(string $timestamp): void
    {
        foreach ($this->starsystems as $system) {
            $system = $system['code'];

            try {
                $response = $this->client->post(self::STARSYSTEM_ENDPOINT.$system);
            } catch (ConnectException $e) {
                app('Log')::critical(
                    'Could not connect to RSI Starmap '.$system,
                    [
                        'message' => $e->getMessage(),
                    ]
                );

                return;
            }

            try {
                $starsystemData = json_decode($response->getBody()->getContents(), true);
                if ($this->checkIfDataCanBeProcessed($starsystemData, static::CELESTIAL_OBJECTS_CHECKLIST)) {
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
            }

            Storage::disk(self::STARSYSTEM_DISK)->put(
                sprintf('%s/%s_system.json', $timestamp, $system),
                json_encode($celestialObjects)
            );
        }
    }
}
