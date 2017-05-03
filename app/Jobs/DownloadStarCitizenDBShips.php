<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use function GuzzleHttp\Psr7\stream_for;

/**
 * Class DownloadStarCitizenDBShips
 * @package App\Jobs
 */
class DownloadStarCitizenDBShips implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const STAR_CITIZEN_DB_URL = 'http://starcitizendb.com/';

    private const FILES_TO_SKIP = [
        'RSI_Constellation_SCItemTest.json',
        'MITE_SimPod.json',
        'GRIN_PTV.json',
    ];

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        App::make('Log')->info('Starting Ship Download Job');
        $client = new Client([
            'timeout' => 10.0,
        ]);

        $urls = (String) $client->get(self::STAR_CITIZEN_DB_URL.'api/ships/specs')->getBody();
        preg_match_all('/href="([^\'\"]+)/', $urls, $urls);
        $urls = $urls[1];

        foreach ($urls as $url) {
            $fileName = explode('/', $url);
            $fileName = end($fileName);

            if (!in_array($fileName, self::FILES_TO_SKIP)) {
                $resource = fopen(config('filesystems.disks.scdb_ships_base.root').'/'.$fileName, 'w');
                $stream = stream_for($resource);
                $client->request(
                    'GET',
                    self::STAR_CITIZEN_DB_URL.$url,
                    ['save_to' => $stream]
                );
                App::make('Log')->info('Downloading '.$fileName);
            }
        }
        App::make('Log')->info('Ship Download Job Finished');

        App::make('Log')->info('Dispatching Split Files Job');
        dispatch(new SplitShipFiles());
    }
}
