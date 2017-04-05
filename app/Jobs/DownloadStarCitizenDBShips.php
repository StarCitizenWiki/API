<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use function GuzzleHttp\Psr7\stream_for;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

/**
 * Class DownloadStarCitizenDBShips
 * @package App\Jobs
 */
class DownloadStarCitizenDBShips implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const STARCITIZENDB_URL = 'http://starcitizendb.com/';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Starting Ship Download Job');
        $client = new Client([
            'timeout' => 10.0,
        ]);

        $urls = (String) $client->get(DownloadStarCitizenDBShips::STARCITIZENDB_URL.'api/ships/specs')->getBody();
        preg_match_all('/href="([^\'\"]+)/', $urls, $urls);
        $urls = $urls[1];
        foreach ($urls as $url) {
            $fileName = explode('/', $url);
            $fileName = end($fileName);
            $resource = fopen(config('filesystems.disks.scdb_ships.root').'/'.$fileName, 'w');
            $stream = stream_for($resource);
            $client->request(
                'GET',
                DownloadStarCitizenDBShips::STARCITIZENDB_URL.$url,
                ['save_to' => $stream]
            );
            Log::info('Downloading '.$fileName);
        }
        Log::info('Ship Download Job Finished');
    }
}
