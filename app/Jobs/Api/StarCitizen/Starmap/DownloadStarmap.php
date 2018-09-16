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

//        $this->setSystems();
//        foreach ($this->starsystems as $system) {
//            $this->writeStarmapContentToDB($system);
//        }

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


    /**
     * Sets the systems
     */
    private function setSystems(): void
    {
        $overviewData = $this->getJsonArrayFromStarmap('bootup/');
        if ($this->checkIfDataCanBeProcessed($overviewData, static::OVERVIEWDATA_CHECKLIST)) {
            $this->starsystems = $overviewData['data']['systems']['resultset'];
        } else {
            app('Log')::error('Can not read Systems from RSI');
        }
    }

    /**
     * Gets JSON from Starmap and returns it as array
     *
     * @param string $uri
     *
     * @return array
     */
    private function getJsonArrayFromStarmap(
        string $uri
    ): array {
        $response = $this->client->request('POST', config('api.rsi_url').'/starmap/'.$uri);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param string $system
     */
    private function writeStarmapContentToDB($system): void {
        $systemId = $this->writeStarsystemToDB($system);

        app('Log')::info("Read Celestial Objects of {$system['code']} (Id: {$systemId})");
        $celestialObjects = $this->getCelestialObjects($system['code']);
        foreach ($celestialObjects as $celestialObject) {
            $this->writeCelestialObjectToDb($celestialObject, $systemId);
        }
    }

    /**
     * @param string $system
     *
     * @return int
     */
    private function writeStarsystemToDB($system): int {
        $systemId = null;
        $lastStarsystem = null;
        $starsystemQueryData = Starsystem::where('code', $system['code'])->orderBy('created_at', 'DESC')->first();
        if (!is_null($starsystemQueryData)) {
            $lastStarsystem = $starsystemQueryData->toArray();
        }

        if (is_null($lastStarsystem) || strcmp($lastStarsystem['cig_time_modified'], $system['time_modified']) !== 0) {
            app('Log')::info('Write to Database System '.$system['code']);

            //TODO durch update ersetzen
            Starsystem::create(
                [
                    'code'                      => $system['code'],
                    'cig_id'                    => $system['id'],
                    'status'                    => $system['status'],
                    'cig_time_modified'         => $system['time_modified'],
                    'type'                      => $system['type'],
                    'name'                      => $system['name'],
                    'position_x'                => $system['position_x'],
                    'position_y'                => $system['position_y'],
                    'position_z'                => $system['position_z'],
                    'info_url'                  => $system['info_url'],
                    'description'               => $system['description'],
                    'affiliation_id'            => $system['affiliation'][0]['id'],
                    'affiliation_name'          => $system['affiliation'][0]['name'],
                    'affiliation_code'          => $system['affiliation'][0]['code'],
                    'affiliation_color'         => $system['affiliation'][0]['color'],
                    'affiliation_membership_id' => $system['affiliation'][0]['membership.id'],
                    'aggregated_size'           => $system['aggregated_size'],
                    'aggregated_population'     => $system['aggregated_population'],
                    'aggregated_economy'        => $system['aggregated_economy'],
                    'aggregated_danger'         => $system['aggregated_danger'],
                    'sourcedata'                => json_encode($system),
                ]
            );

            $this->starsystemsUpdated++;
            $systemId = $system['id'];
        } else {
            $systemId = $lastStarsystem['cig_id'];
        }

        return intval($systemId);
    }

    /**
     * @param string $starsystemName
     *
     * @return array
     */
    private function getCelestialObjects(
        $starsystemName
    ): array {
        $allCelestialObjects = [];
        $starsystemData = $this->getJsonArrayFromStarmap('star-systems/'.$starsystemName);
        if ($this->checkIfDataCanBeProcessed($starsystemData, static::CELESTIALOBJECTS_CHECKLIST)) {
            $celestialObjects = $starsystemData['data']['resultset'][0]['celestial_objects'];
            $allCelestialObjects = $this->addCelestialSubobjects($celestialObjects);
        } else {
            app('Log')::error("Can not read System {$starsystemName} from RSI");
        }

        return $allCelestialObjects;
    }

    /**
     * @param array $celestialObjects
     *
     * @return array
     */
    private function addCelestialSubobjects(
        $celestialObjects
    ): array {
        foreach ($celestialObjects as $celestialObject) {
            if (in_array($celestialObject['type'], self::CELESTIAL_SUBOBJECTS_REQUEST)) {
                $celestialContent = $this->getJsonArrayFromStarmap('celestial-objects/'.$celestialObject['code']);
                $celestialObjects = array_merge($celestialObjects, $this->getCelestialSubobjects($celestialContent));
            }
        }

        return $celestialObjects;
    }

    /**
     * @param array $celestialContent
     *
     * @return array
     */
    private function getCelestialSubobjects(
        $celestialContent
    ): array {
        $celestialSubobjects = [];
        if ($this->checkIfDataCanBeProcessed($celestialContent, static::CELESTIALSUBOBJECTS_CHECKLIST)) {
            foreach ($celestialContent['data']['resultset'][0]['children'] as $celestialChildren) {
                if (in_array($celestialChildren['type'], self::CELESTIAL_SUBOBJECTS_TYPE)) {
                    array_push($celestialSubobjects, $celestialChildren);
                }
            }
        }

        return $celestialSubobjects;
    }

    /**
     * @param array $celestialObject
     * @param int   $systemId
     */
    private function writeCelestialObjectToDb(
        $celestialObject,
        $systemId
    ): void {
        $lastCelestialObject = null;
        $celestialObjectQueryData = CelestialObject::where('code', $celestialObject['code'])->orderBy(
            'cig_time_modified',
            'DESC'
        )->first();
        if (!is_null($celestialObjectQueryData)) {
            $lastCelestialObject = $celestialObjectQueryData->toArray();
        }

        if (is_null($lastCelestialObject) || strcmp(
                $lastCelestialObject['cig_time_modified'],
                $celestialObject['time_modified']
            ) !== 0) {
            app('Log')::info('Write to Database CelestialObject '.$celestialObject['code']);

            $celestialObjectModel = CelestialObject::create(
                [
                    'code'              => $celestialObject['code'],
                    'cig_id'            => $celestialObject['id'],
                    'cig_system_id'     => $systemId,
                    'cig_time_modified' => $celestialObject['time_modified'],
                    'type'              => $celestialObject['type'],
                    'designation'       => $celestialObject['designation'],
                    'name'              => $celestialObject['name'],
                    'age'               => $celestialObject['age'],
                    'distance'          => $celestialObject['distance'],
                    'latitude'          => $celestialObject['latitude'],
                    'longitude'         => $celestialObject['longitude'],
                    'axial_tilt'        => $celestialObject['axial_tilt'],
                    'orbit_period'      => $celestialObject['orbit_period'],
                    'description'       => $celestialObject['description'],
                    'info_url'          => $celestialObject['info_url'],
                    'habitable'         => $celestialObject['habitable'],
                    'fairchanceact'     => $celestialObject['fairchanceact'],
                    'show_orbitlines'   => $celestialObject['show_orbitlines'],
                    'show_label'        => $celestialObject['show_label'],
                    'appearance'        => $celestialObject['appearance'],
                    'sensor_population' => $celestialObject['sensor_population'],
                    'sensor_economy'    => $celestialObject['sensor_economy'],
                    'sensor_danger'     => $celestialObject['sensor_danger'],
                ]
            );

            if (!is_null($celestialObject['shader_data']) && is_array($celestialObject['shader_data'])) {
                $celestialObjectModel->shader_data = json_encode($celestialObject['shader_data']);
            }

            $celestialObjectModel->size = $celestialObject['size'];
            $celestialObjectModel->parent_id = $celestialObject['parent_id'];

            if (!is_null($celestialObject['subtype']) && is_array($celestialObject['subtype'])) {
                $celestialObjectModel->subtype_id = $celestialObject['subtype']['id'];
                $celestialObjectModel->subtype_name = $celestialObject['subtype']['name'];
                $celestialObjectModel->subtype_type = $celestialObject['subtype']['type'];
            }

            if (!is_null($celestialObject['affiliation']) && is_array(
                    $celestialObject['affiliation']
                ) && array_key_exists(0, $celestialObject['affiliation'])) {
                $celestialObjectModel->affiliation_id = $celestialObject['affiliation'][0]['id'];
                $celestialObjectModel->affiliation_name = $celestialObject['affiliation'][0]['name'];
                $celestialObjectModel->affiliation_code = $celestialObject['affiliation'][0]['code'];
                $celestialObjectModel->affiliation_color = $celestialObject['affiliation'][0]['color'];
                $celestialObjectModel->affiliation_membership_id = $celestialObject['affiliation'][0]['membership.id'];
            }

            if (is_array($celestialObject['population']) && count($celestialObject['population']) > 0) {
                $celestialObjectModel->population = json_encode($celestialObject['population']);
            }
            $celestialObjectModel->sourcedata = json_encode($celestialObject);

            $celestialObjectModel->save();
            $this->celestialObjectsUpdated++;
        }
    }
}