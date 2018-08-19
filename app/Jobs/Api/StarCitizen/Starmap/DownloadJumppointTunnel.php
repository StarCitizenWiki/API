<?php declare(strict_types = 1);
/**
 * User: Keonie
 * Date: 03.08.2017 16:49
 */

namespace App\Jobs\StarCitizen\Starmap;

use App\Jobs\AbstractBaseDownloadData;
use App\Models\Api\StarCitizen\Starmap\Jumppoint;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class DownloadJumppointTunnel
 */
class DownloadJumppointTunnel extends AbstractBaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const OVERVIEWDATA_CHECKLIST = ['data', 'tunnels', 'resultset', 0];
    private $jumppointtunnels;
    private $jumppointtunnelsUpdated = 0;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info('Starting Jumppoint Tunnel Download Job');
        $this->client = new Client(['timeout' => 10.0]);

        $this->setJumppointtunnels();
        foreach ($this->jumppointtunnels as $jumppointtunnel) {
            $this->writeJumppointtunnelToDB($jumppointtunnel);
        }
        app('Log')::info("Jumppoint Tunnel Download Job Finished (updated:{$this->jumppointtunnelsUpdated})");
    }

    /**
     * Sets JumpPoints
     */
    private function setJumppointtunnels()
    {
        $overviewData = $this->getJsonArrayFromStarmap('bootup/');
        if ($this->checkIfDataCanBeProcessed($overviewData, static::OVERVIEWDATA_CHECKLIST)) {
            $this->jumppointtunnels = $overviewData['data']['tunnels']['resultset'];
        } else {
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
        $response = $this->client->request('POST', config('api.rsi_url').'/starmap/'.$uri);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param string $jumppointtunnel
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

        if (is_null($lastJumppointtunnelSource) ||
            strcmp(
                $jumppointtunnelSourceData,
                $lastJumppointtunnelSource
            ) !== 0) {
            app('Log')::info("Write to Database Jumppointtunnel {$jumppointtunnel['id']}");

            Jumppoint::create(
                [
                    'cig_id' => $jumppointtunnel['id'],
                    'size' => $jumppointtunnel['size'],
                    'direction' => $jumppointtunnel['direction'],
                    'entry_cig_id' => $jumppointtunnel['entry']['id'],
                    'entry_cig_system_id' => $jumppointtunnel['entry']['star_system_id'],
                    'entry_code' => $jumppointtunnel['entry']['code'],
                    'entry_designation' => $jumppointtunnel['entry']['designation'],
                    'entry_status' => $jumppointtunnel['entry']['status'],
                    'exit_cig_id' => $jumppointtunnel['exit']['id'],
                    'exit_cig_system_id' => $jumppointtunnel['exit']['star_system_id'],
                    'exit_code' => $jumppointtunnel['exit']['code'],
                    'exit_designation' => $jumppointtunnel['exit']['designation'],
                    'exit_status' => $jumppointtunnel['exit']['status'],
                    'sourcedata' => $jumppointtunnelSourceData,
                ]
            );

            $this->jumppointtunnelsUpdated++;
        }
    }
}
