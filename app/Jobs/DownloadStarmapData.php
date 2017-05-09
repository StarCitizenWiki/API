<?php

namespace App\Jobs;

use App\Models\Starsystem;
use App\Repositories\StarCitizen\APIv1\StarmapRepository;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Class DownloadStarmapData
 * @package App\Jobs
 */
class DownloadStarmapData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Requests for Celestial Subobject
    const CELESTIAL_SUBOBJECTS_REQUEST = ['PLANET'];
    // Add Type to Starmapdata
    const CELESTIAL_SUBOBJECTS_TYPE = ['LZ'];

    /**
     * @var Client
     */
    private $guzzleClient;

    /**
     * @var array
     */
    private $starmapContent;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() : void
    {
        Log::info('Starting Starmap Download Job');
        $this->guzzleClient = new Client(['timeout' => 10.0]);

        foreach (Starsystem::where('exclude', '=', false)->get() as $system) {
            $fileName = Starsystem::makeFilenameFromCode($system->code);
            Log::info('Downloading '.$system->code);
            $this->starmapContent = $this->getJsonArrayFromStarmap('star-systems/'.$system->code);

            if ($this->checkIfDataCanBeProcessed($this->starmapContent) &&
                array_key_exists('celestial_objects', $this->starmapContent['data']['resultset'][0])) {
                $this->addCelestialContent();
            }

            Log::info('Writing System to file '.$system->code);
            Storage::disk('starmap')->put(
                $fileName,
                json_encode($this->starmapContent, JSON_PRETTY_PRINT)
            );
        }
        Log::info('Starmap Download Job Finished');
    }

    /**
     * Download Celestrial Objects from CIG and add it to the Starmap
     */
    private function addCelestialContent() : void
    {
        foreach ($this->starmapContent['data']['resultset'][0]['celestial_objects'] as $celestialObject) {
            if (in_array($celestialObject['type'], self::CELESTIAL_SUBOBJECTS_REQUEST)) {
                $celestialContent = $this->getJsonArrayFromStarmap('celestial-objects/'.$celestialObject['code']);
                $this->addCelestialSubobjectsToStarmap($celestialContent);
            }
        }
    }

    /**
     * Add CELESTIAL_SUBOBJECTS_TYPE to Starmapdata
     *
     * @param $celestialContent array Celstrial Object
     */
    private function addCelestialSubobjectsToStarmap($celestialContent) : void
    {
        if ($this->checkIfDataCanBeProcessed($celestialContent) &&
            array_key_exists('children', $celestialContent['data']['resultset'][0])) {
            foreach ($celestialContent['data']['resultset'][0]['children'] as $celestrialChildren) {
                if (in_array($celestrialChildren['type'], self::CELESTIAL_SUBOBJECTS_TYPE)) {
                    array_push($this->starmapContent['data']['resultset'][0]['celestial_objects'], $celestrialChildren);
                }
            }
        }
    }

    /**
     * Gets JSON from Starmap and returns it as array
     *
     * @param String $uri
     *
     * @return array
     */
    private function getJsonArrayFromStarmap(String $uri) : array
    {
        $response = $this->guzzleClient->request('POST', StarmapRepository::API_URL.'starmap/'.$uri);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Checks if provided data array can be processed
     *
     * @param $data
     *
     * @return bool
     */
    private function checkIfDataCanBeProcessed($data) : bool
    {
        return is_array($data) &&
                $data['success'] === 1 &&
                array_key_exists('data', $data) &&
                array_key_exists('resultset', $data['data']) &&
                array_key_exists(0, $data['data']['resultset']);
    }

    /**
     * [WIP]
     */
    private function writeStarmapContentToDB() : void
    {
        $this->writeStarsystemsToDB();
    }

    private function writeStarsystemsToDB() : void
    {
        DB::table('starsystems')->insert([
            'cig_id'                          => $this->starmapContent['data']['resultset']['id'],
            'status'                          => $this->starmapContent['data']['resultset']['status'],
            'cig_time_modified'               => $this->starmapContent['data']['resultset']['cig_time_modified'],
            'type'                            => $this->starmapContent['data']['resultset']['type'],
            'name'                            => $this->starmapContent['data']['resultset']['name'],
            'position_x'                      => $this->starmapContent['data']['resultset']['position_x'],
            'position_y'                      => $this->starmapContent['data']['resultset']['position_y'],
            'position_z'                      => $this->starmapContent['data']['resultset']['position_z'],
            'info_url'                        => $this->starmapContent['data']['resultset']['info_url'],
            'habitable_zone_inner'            => $this->starmapContent['data']['resultset']['habitable_zone_inner'],
            'habitable_zone_outer'            => $this->starmapContent['data']['resultset']['habitable_zone_outer'],
            'frost_line'                      => $this->starmapContent['data']['resultset']['frost_line'],
            'description'                     => $this->starmapContent['data']['resultset']['description'],
            'shader_data_lightColor'          => $this->starmapContent['data']['resultset']['shader_data_lightColor'],
            'shader_data_starfield_radius'    => $this->starmapContent['data']['resultset']['shader_data_starfield_radius'],
            'shader_data_starfield_count'     => $this->starmapContent['data']['resultset']['shader_data_starfield_count'],
            'shader_data_starfield_sizeMin'   => $this->starmapContent['data']['resultset']['shader_data_starfield_sizeMin'],
            'shader_data_starfield_sizeMax'   => $this->starmapContent['data']['resultset']['shader_data_starfield_sizeMax'],
            'shader_data_starfield_color1'    => $this->starmapContent['data']['resultset']['shader_data_starfield_color1'],
            'shader_data_starfield_color2'    => $this->starmapContent['data']['resultset']['shader_data_starfield_color2'],
            'shader_data_planetsSize_min'     => $this->starmapContent['data']['resultset']['shader_data_planetsSize_min'],
            'shader_data_planetsSize_max'     => $this->starmapContent['data']['resultset']['shader_data_planetsSize_max'],
            'shader_data_planetsSize_kFactor' => $this->starmapContent['data']['resultset']['shader_data_planetsSize_kFactor'],
            'affiliation_id'                  => $this->starmapContent['data']['resultset']['affiliation_id'],
            'affiliation_name'                => $this->starmapContent['data']['resultset']['affiliation_name'],
            'affiliation_code'                => $this->starmapContent['data']['resultset']['affiliation_code'],
            'affiliation_color'               => $this->starmapContent['data']['resultset']['affiliation_color'],
            'affiliation_membership_id'       => $this->starmapContent['data']['resultset']['affiliation_membership_id'],
            'aggregated_size'                 => $this->starmapContent['data']['resultset']['aggregated_size'],
            'aggregated_population'           => $this->starmapContent['data']['resultset']['aggregated_population'],
            'aggregated_economy'              => $this->starmapContent['data']['resultset']['aggregated_economy'],
            'aggregated_danger'               => $this->starmapContent['data']['resultset']['aggregated_danger'],
            'sourcedata'                      => $this->starmapContent['data']['resultset']['sourcedata']
        ]);
    }
}
