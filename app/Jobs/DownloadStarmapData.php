<?php

namespace App\Jobs;

use App\Models\Starsystem;
use App\Repositories\StarCitizen\APIv1\StarmapRepository;
use GuzzleHttp\Client;
use function GuzzleHttp\Psr7\stream_for;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

/**
 * Class DownloadStarmapData
 * @package App\Jobs
 */
class DownloadStarmapData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Starting Starmap Download Job');
        $client = new Client([
            'timeout' => 10.0,
        ]);

        foreach (Starsystem::where('exclude', '=', false)->get() as $system) {
            $fileName = Starsystem::makeFilenameFromCode($system->code);
            $resource = fopen(config('filesystems.disks.starmap.root').'/'.$fileName, 'w');
            $stream = stream_for($resource);
            $client->request(
                'POST',
                StarmapRepository::API_URL.'starmap/star-systems/'.$system->code,
                ['save_to' => $stream]
            );
            Log::info('Downloading '.$system->code);
        }
        Log::info('Starmap Download Job Finished');
    }
}
