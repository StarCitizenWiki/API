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

	// Requests for Celestrial Subobject
	const CELESTIAL_SUBOBJECTS_REQUEST = array('PLANET');
	// Add Type to Starmapdata
	const CELESTIAL_SUBOBJECTS_TYPE = array('LZ');

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		Log::info('Starting Starmap Download Job');
		$client = new Client(['timeout' => 10.0,]);

		foreach(Starsystem::where('exclude', '=', false)->get() as $system)
		{
			$fileName = Starsystem::makeFilenameFromCode($system->code);
			Log::info('Downloading ' . $system->code);
			$starmapResponse = $client->request('POST', StarmapRepository::API_URL . 'starmap/star-systems/' . $system->code);
			$starmapContent = json_decode($starmapResponse->getBody()->getContents(), true);

			$this->addCelestrialContent($client, $starmapContent);

			Log::info('Writing System to file ' . $system->code);
			Storage::disk('starmap')->put(
				$fileName,
				json_encode($starmapContent, JSON_PRETTY_PRINT)
			);
		}
		Log::info('Starmap Download Job Finished');
	}

	/**
	 * Download Celestrial Objects from CIG and add it to the Starmap
	 * @param $client GuzzleHttp\Client
	 * @param $starmapContent Json Starmapdata for one System from CIG
	 */
	private function addCelestrialContent($client, &$starmapContent)
	{
		if(is_array($starmapContent) && $starmapContent['success'] === 1
			&& array_key_exists('data', $starmapContent)
			&& array_key_exists('resultset', $starmapContent['data'])
			&& array_key_exists(0, $starmapContent['data']['resultset'])
			&& array_key_exists('celestial_objects', $starmapContent['data']['resultset'][0]))
		{
			foreach($starmapContent['data']['resultset'][0]['celestial_objects'] as $celestialObject)
			{
				if(in_array($celestialObject['type'], self::CELESTIAL_SUBOBJECTS_REQUEST))
				{
					$celestialResponse = $client->request('POST', StarmapRepository::API_URL . 'starmap/celestial-objects/' . $celestialObject['code']);
					$celestialContent = json_decode($celestialResponse->getBody()->getContents(), true);
					$this->addCelestrialSubobjectsToStarmap($celestialContent, $starmapContent);
				}
			}
		}
	}

	/**
	 * Add CELESTIAL_SUBOBJECTS_TYPE to Starmapdata
	 * @param $celestialContent Json Celstrial Object
	 * @param $starmapContent Json Starmapdata for one System from CIG
	 */
	private function addCelestrialSubobjectsToStarmap($celestialContent, &$starmapContent)
	{
		if(is_array($celestialContent) && $celestialContent['success'] === 1
			&& array_key_exists('data', $celestialContent)
			&& array_key_exists('resultset', $celestialContent['data'])
			&& array_key_exists(0, $celestialContent['data']['resultset'])
			&& array_key_exists('children', $celestialContent['data']['resultset'][0]))
		{
			foreach($celestialContent['data']['resultset'][0]['children'] as $celestrialChildren)
			{
				if(in_array($celestrialChildren['type'], self::CELESTIAL_SUBOBJECTS_TYPE))
				{
					array_push($starmapContent['data']['resultset'][0]['celestial_objects'], $celestrialChildren);
				}
			}
		}
	}
}
