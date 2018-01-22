<?php
/**
 * User: Keonie
 * Date: 03.08.2017 16:49
 */

namespace App\Jobs;

use App\Models\Jumppoint;
use App\Repositories\StarCitizen\APIv1\StarmapRepository;
use App\Traits\ProfilesMethodsTrait;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class DownloadJumppointTunnel
 * @package App\Jobs
 */
class DownloadJumppointTunnel extends AbstractBaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use ProfilesMethodsTrait;

    private $jumppointtunnels;
    private $jumppointtunnels_updated = 0;

    const OVERVIEWDATA_CHECKLIST = ['data', 'tunnels', 'resultset', 0];

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->startProfiling(__FUNCTION__);

        app('Log')::info('Starting Jumppoint Tunnel Download Job');
        $this->guzzleClient = new Client(['timeout' => 10.0]);

        $this->setJumppointtunnels();
        foreach ($this->jumppointtunnels as $jumppointtunnel) {
            $this->writeJumppointtunnelToDB($jumppointtunnel);
        }
        app('Log')::info("Jumppoint Tunnel Download Job Finished (updated:{$this->jumppointtunnels_updated})");
    }

    private function setJumppointtunnels()
    {
        $overviewData = $this->getJsonArrayFromStarmap('bootup/');
        if ($this->checkIfDataCanBeProcessed($overviewData, static::OVERVIEWDATA_CHECKLIST)) {
            $this->jumppointtunnels = $overviewData['data']['tunnels']['resultset'];
        }
        else {
            app('Log')::error('Can not read Jumppoint Tunnels from RSI');
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
     * @param $jumppointtunnel
     */
    private function writeJumppointtunnelToDB($jumppointtunnel): void
    {
        $lastJumppointtunnelSource = null;
        $jumppointtunnelQueryData = Jumppoint::where('cig_id', $jumppointtunnel['id'])->orderBy(
            'created_at',
            'DESC'
        )->first();
        if (!is_null($jumppointtunnelQueryData)) {
            $lastJumppointtunnel = $jumppointtunnelQueryData->toArray();
            $lastJumppointtunnelSource = $lastJumppointtunnel['sourcedata'];
        }
        $jumppointtunnelSourceData = json_encode($jumppointtunnel);

        if (is_null($lastJumppointtunnelSource)
        || strcmp($jumppointtunnelSourceData, $lastJumppointtunnelSource) != 0) {
            app('Log')::info("Write to Database Jumppointtunnel {$jumppointtunnel['id']}");

            $jumppointModel = new Jumppoint();
            $jumppointModel->cig_id = $jumppointtunnel['id'];
            $jumppointModel->size = $jumppointtunnel['size'];
            $jumppointModel->direction = $jumppointtunnel['direction'];
            $jumppointModel->entry_cig_id = $jumppointtunnel['entry']['id'];
            $jumppointModel->entry_cig_system_id = $jumppointtunnel['entry']['star_system_id'];
            $jumppointModel->entry_code = $jumppointtunnel['entry']['code'];
            $jumppointModel->entry_designation = $jumppointtunnel['entry']['designation'];
            $jumppointModel->entry_status = $jumppointtunnel['entry']['status'];
            $jumppointModel->exit_cig_id = $jumppointtunnel['exit']['id'];
            $jumppointModel->exit_cig_system_id = $jumppointtunnel['exit']['star_system_id'];
            $jumppointModel->exit_code = $jumppointtunnel['exit']['code'];
            $jumppointModel->exit_designation = $jumppointtunnel['exit']['designation'];
            $jumppointModel->exit_status = $jumppointtunnel['exit']['status'];

            $jumppointModel->sourcedata = $jumppointtunnelSourceData;
            $jumppointModel->save();

            $this->jumppointtunnels_updated++;
        }
    }
}