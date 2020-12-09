<?php

namespace App\Jobs\Api\StarCitizen\Vehicle\Import;

use App\Jobs\Api\StarCitizen\AbstractRSIDownloadData;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Import all msrps by requesting the pledge-store upgrade api endpoint
 */
class ImportMsrp extends AbstractRSIDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->makeClient();

        $query = <<<QUERY
{
    ships {
      id
      name
      msrp
    }
}
QUERY;

        self::$client->post('https://robertsspaceindustries.com/api/account/v2/setAuthToken');
        self::$client->post('https://robertsspaceindustries.com/api/ship-upgrades/setContextToken');
        $response = self::$client->post(
            'https://robertsspaceindustries.com/pledge-store/api/upgrade',
            [
                'query' => $query,
            ]
        );

        if (!$response->ok()) {
            app('Log')::error('Could not connect to RSI Pledge Store API, retrying in 5 minutes.');

            $this->release(300);
        }

        collect($response->json('data.ships', []))
            ->each(
                function (array $vehicle) {
                    $model = Vehicle::query()->where('cig_id', $vehicle['id'])->first();

                    if ($model !== null && $vehicle['msrp'] !== null) {
                        $model->update(
                            [
                                'msrp' => substr($vehicle['msrp'], 0, -2),
                            ]
                        );
                    }
                }
            );
    }
}
