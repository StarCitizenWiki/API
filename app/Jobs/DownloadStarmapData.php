<?php

namespace App\Jobs;

use App\Models\Starsystem;
use App\Repositories\StarCitizen\APIv1\StarmapRepository;
use App\Traits\ProfilesMethodsTrait;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Class DownloadStarmapData
 * @package App\Jobs
 */
class DownloadStarmapData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ProfilesMethodsTrait;

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
        $this->startProfiling(__FUNCTION__);

        app('Log')::info('Starting Starmap Download Job');
        $this->guzzleClient = new Client(['timeout' => 10.0]);

        foreach (Starsystem::where('exclude', '=', false)->get() as $system) {
            $fileName = Starsystem::makeFilenameFromCode($system->code);
            $this->addTrace(__FUNCTION__, "Downloading {$system->code}");
            $this->starmapContent = $this->getJsonArrayFromStarmap('star-systems/'.$system->code);

            if ($this->checkIfDataCanBeProcessed($this->starmapContent) &&
                array_key_exists('celestial_objects', $this->starmapContent['data']['resultset'][0])) {
                $this->addCelestialContent();
            }

            $this->addTrace(__FUNCTION__, "Writing System to file {$system->code}");
            Storage::disk('starmap')->put(
                $fileName,
                json_encode($this->starmapContent, JSON_PRETTY_PRINT)
            );
        }
        app('Log')::info('Starmap Download Job Finished');

        $this->stopProfiling(__FUNCTION__);
    }

    /**
     * Download Celestrial Objects from CIG and add it to the Starmap
     */
    private function addCelestialContent() : void
    {
        $this->startProfiling(__FUNCTION__);

        foreach ($this->starmapContent['data']['resultset'][0]['celestial_objects'] as $celestialObject) {
            if (in_array($celestialObject['type'], self::CELESTIAL_SUBOBJECTS_REQUEST)) {
                $celestialContent = $this->getJsonArrayFromStarmap('celestial-objects/'.$celestialObject['code']);
                $this->addCelestialSubobjectsToStarmap($celestialContent);
            }
        }

        $this->stopProfiling(__FUNCTION__);
    }

    /**
     * Add CELESTIAL_SUBOBJECTS_TYPE to Starmapdata
     *
     * @param $celestialContent array Celstrial Object
     */
    private function addCelestialSubobjectsToStarmap($celestialContent) : void
    {
        $this->startProfiling(__FUNCTION__);

        if ($this->checkIfDataCanBeProcessed($celestialContent) &&
            array_key_exists('children', $celestialContent['data']['resultset'][0])) {
            foreach ($celestialContent['data']['resultset'][0]['children'] as $celestrialChildren) {
                if (in_array($celestrialChildren['type'], self::CELESTIAL_SUBOBJECTS_TYPE)) {
                    array_push($this->starmapContent['data']['resultset'][0]['celestial_objects'], $celestrialChildren);
                }
            }
        }

        $this->stopProfiling(__FUNCTION__);
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
}
