<?php

declare(strict_types=1);

namespace App\Jobs\Api\StarCitizen\Starmap\Download;

use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use App\Models\Api\StarCitizen\Starmap\Jumppoint\Jumppoint;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class DownloadJumppointTunnel
 */
class DownloadJumppointTunnel extends BaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const OVERVIEWDATA_CHECKLIST = ['data', 'tunnels', 'resultset', 0];
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
        if ($this->checkDataStructureIsValid($overviewData, static::OVERVIEWDATA_CHECKLIST)) {
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
     *
     * @throws GuzzleException
     */
    private function getJsonArrayFromStarmap(string $uri): array
    {
        $response = self::$client->request('POST', config('api.rsi_url') . '/starmap/' . $uri);

        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
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
        if ($jumppointtunnelQueryData !== null) {
            $lastJumppointtunnel = $jumppointtunnelQueryData->toArray();
            $lastJumppointtunnelSource = $lastJumppointtunnel['sourcedata'];
        }
        $jumppointtunnelSourceData = json_encode($jumppointtunnel, JSON_THROW_ON_ERROR);

        $strCmp = strcmp($jumppointtunnelSourceData, $lastJumppointtunnelSource) !== 0;

        if ($lastJumppointtunnelSource === null || $strCmp) {
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
