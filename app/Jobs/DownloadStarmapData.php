<?php declare(strict_types = 1);

namespace App\Jobs;

use App\Models\CelestialObject;
use App\Models\Starsystem;
use App\Repositories\StarCitizen\ApiV1\StarmapRepository;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class DownloadStarmapData
 *
 * @package App\Jobs
 */
class DownloadStarmapData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    // Requests for Celestial Subobject
    const CELESTIAL_SUBOBJECTS_REQUEST = ['PLANET'];
    // Add Type to Starmapdata
    const CELESTIAL_SUBOBJECTS_TYPE = ['LZ'];

    /**
     * @var \GuzzleHttp\Client
     */
    private $guzzleClient;

    private $starsystems;

    private $starsystemsUpdated = 0;
    private $celestialObjectsUpdated = 0;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info('Starting Starmap Download Job');
        $this->guzzleClient = new Client(['timeout' => 10.0]);

        $this->setSystems();
        foreach ($this->starsystems as $system) {
            $this->writeStarmapContentToDB($system);
        }

        //TODO Mail an api@startcitizen.wiki und ins log mit Anzahl wieviel System und Celestial Objects Updated

        app('Log')::info('Starmap Download Job Finished');
    }

    private function setSystems(): void
    {
        $overviewData = $this->getJsonArrayFromStarmap('bootup/');
        if ($this->checkIfOverviewDataCanBeProcessed($overviewData)) {
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
    private function getJsonArrayFromStarmap(string $uri): array
    {
        $response = $this->guzzleClient->request('POST', StarmapRepository::API_URL.'starmap/'.$uri);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param $data
     *
     * @return bool
     */
    private function checkIfOverviewDataCanBeProcessed($data): bool
    {
        return is_array($data) &&
            $data['success'] === 1 &&
            array_key_exists('data', $data) &&
            array_key_exists('systems', $data['data']) &&
            array_key_exists('resultset', $data['data']['systems']) &&
            array_key_exists(0, $data['data']['systems']['resultset']);
    }

    /**
     * @param $system
     */
    private function writeStarmapContentToDB($system): void
    {
        $systemId = $this->writeStarsystemToDB($system);

        app('Log')::info('Read Celestial Objets of '.$system['code'].' (Id: '.$systemId.')');
        $celestialObjects = $this->getCelestialObjects($system['code']);
        foreach ($celestialObjects as $celestialObject) {
            $this->writeCelestialObjectToDb($celestialObject, $systemId);
        }
    }

    /**
     * @param $system
     *
     * @return int
     */
    private function writeStarsystemToDB($system): int
    {
        $systemId = null;
        $lastStarsystem = null;
        $starsystemQueryData = Starsystem::where('code', $system['code'])->orderBy('created_at', 'DESC')->first();
        if (!is_null($starsystemQueryData)) {
            $lastStarsystem = $starsystemQueryData->toArray();
        }

        if (is_null($lastStarsystem) || strcmp($lastStarsystem['cig_time_modified'], $system['time_modified']) != 0) {
            app('Log')::info('Write to Database System '.$system['code']);
            $starsystem = new Starsystem();
            $starsystem->code = $system['code'];
            $starsystem->cig_id = $system['id'];
            $starsystem->status = $system['status'];
            $starsystem->cig_time_modified = $system['time_modified'];
            $starsystem->type = $system['type'];
            $starsystem->name = $system['name'];
            $starsystem->position_x = $system['position_x'];
            $starsystem->position_y = $system['position_y'];
            $starsystem->position_z = $system['position_z'];
            $starsystem->info_url = $system['info_url'];
            $starsystem->description = $system['description'];
            $starsystem->affiliation_id = $system['affiliation'][0]['id'];
            $starsystem->affiliation_name = $system['affiliation'][0]['name'];
            $starsystem->affiliation_code = $system['affiliation'][0]['code'];
            $starsystem->affiliation_color = $system['affiliation'][0]['color'];
            $starsystem->affiliation_membership_id = $system['affiliation'][0]['membership.id'];
            $starsystem->aggregated_size = $system['aggregated_size'];
            $starsystem->aggregated_population = $system['aggregated_population'];
            $starsystem->aggregated_economy = $system['aggregated_economy'];
            $starsystem->aggregated_danger = $system['aggregated_danger'];
            $starsystem->sourcedata = json_encode($system);

            $starsystem->save();
            $this->starsystemsUpdated++;
            $systemId = $system['id'];
        } else {
            $systemId = $lastStarsystem['cig_id'];
        }

        return intval($systemId);
    }

    // TODO change check to variable parameter List, with recursiv check

    /**
     * @param $starsystemName
     *
     * @return array
     */
    private function getCelestialObjects($starsystemName): array
    {
        $allCelestialObjects = [];
        $starsystemData = $this->getJsonArrayFromStarmap('star-systems/'.$starsystemName);
        if ($this->checkIfCelestialObjectsDataCanBeProcessed($starsystemData)) {
            $celestialObjects = $starsystemData['data']['resultset'][0]['celestial_objects'];
            $allCelestialObjects = $this->addCelestialSubobjects($celestialObjects);
        } else {
            app('Log')::error('Can not read System '.$starsystemName.' from RSI');
        }

        return $allCelestialObjects;
    }

    // TODO change check to variable parameter List, with recursiv check

    /**
     * @param $data
     *
     * @return bool
     */
    private function checkIfCelestialObjectsDataCanBeProcessed($data): bool
    {
        return is_array($data) &&
            $data['success'] === 1 &&
            array_key_exists('data', $data) &&
            array_key_exists('resultset', $data['data']) &&
            array_key_exists(0, $data['data']['resultset']) &&
            array_key_exists('celestial_objects', $data['data']['resultset'][0]) &&
            array_key_exists(0, $data['data']['resultset'][0]['celestial_objects']);
    }

    // TODO change check to variable parameter List, with recursiv check

    /**
     * @param $celestialObjects
     *
     * @return array
     */
    private function addCelestialSubobjects($celestialObjects): array
    {
        foreach ($celestialObjects as $celestialObject) {
            if (in_array($celestialObject['type'], self::CELESTIAL_SUBOBJECTS_REQUEST)) {
                $celestialContent = $this->getJsonArrayFromStarmap('celestial-objects/'.$celestialObject['code']);
                $celestialObjects = array_merge($celestialObjects, $this->getCelestialSubobjects($celestialContent));
            }
        }

        return $celestialObjects;
    }

    /**
     * @param $celestialContent
     *
     * @return array
     */
    private function getCelestialSubobjects($celestialContent): array
    {
        $celestialSubobjects = [];
        if ($this->checkIfCelestialSubobjectsDataCanBeProcessed($celestialContent)) {
            foreach ($celestialContent['data']['resultset'][0]['children'] as $celestialChildren) {
                if (in_array($celestialChildren['type'], self::CELESTIAL_SUBOBJECTS_TYPE)) {
                    array_push($celestialSubobjects, $celestialChildren);
                }
            }
        }

        return $celestialSubobjects;
    }

    /**
     * @param $data
     *
     * @return bool
     */
    private function checkIfCelestialSubobjectsDataCanBeProcessed($data): bool
    {
        return is_array($data) &&
            $data['success'] === 1 &&
            array_key_exists('data', $data) &&
            array_key_exists('resultset', $data['data']) &&
            array_key_exists(0, $data['data']['resultset']) &&
            array_key_exists('children', $data['data']['resultset'][0]) &&
            array_key_exists(0, $data['data']['resultset'][0]['children']);
    }

    /**
     * @param $celestialObject
     * @param $systemId
     */
    private function writeCelestialObjectToDb($celestialObject, $systemId): void
    {
        $lastCelestialObject = null;
        $celestialObjectQueryData = CelestialObject::where('code', $celestialObject['code'])->orderBy(
            'cig_time_modified',
            'DESC'
        )->first();
        if (!is_null($celestialObjectQueryData)) {
            $lastCelestialObject = $celestialObjectQueryData->toArray();
        }

        if (is_null($lastCelestialObject) ||
            strcmp($lastCelestialObject['cig_time_modified'], $celestialObject['time_modified']) != 0
        ) {
            app('Log')::info('Write to Database CelestialObject '.$celestialObject['code']);
            $celestialObjectModel = new CelestialObject();
            $celestialObjectModel->code = $celestialObject['code'];
            $celestialObjectModel->cig_id = $celestialObject['id'];
            $celestialObjectModel->cig_system_id = $systemId;
            $celestialObjectModel->cig_time_modified = $celestialObject['time_modified'];
            $celestialObjectModel->type = $celestialObject['type'];
            $celestialObjectModel->designation = $celestialObject['designation'];
            $celestialObjectModel->name = $celestialObject['name'];
            $celestialObjectModel->age = $celestialObject['age'];
            $celestialObjectModel->distance = $celestialObject['distance'];
            $celestialObjectModel->latitude = $celestialObject['latitude'];
            $celestialObjectModel->longitude = $celestialObject['longitude'];
            $celestialObjectModel->axial_tilt = $celestialObject['axial_tilt'];
            $celestialObjectModel->orbit_period = $celestialObject['orbit_period'];
            $celestialObjectModel->description = $celestialObject['description'];
            $celestialObjectModel->info_url = $celestialObject['info_url'];
            $celestialObjectModel->habitable = $celestialObject['habitable'];
            $celestialObjectModel->fairchanceact = $celestialObject['fairchanceact'];
            $celestialObjectModel->show_orbitlines = $celestialObject['show_orbitlines'];
            $celestialObjectModel->show_label = $celestialObject['show_label'];
            $celestialObjectModel->appearance = $celestialObject['appearance'];
            $celestialObjectModel->sensor_population = $celestialObject['sensor_population'];
            $celestialObjectModel->sensor_economy = $celestialObject['sensor_economy'];
            $celestialObjectModel->sensor_danger = $celestialObject['sensor_danger'];

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
