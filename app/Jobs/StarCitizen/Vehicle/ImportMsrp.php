<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Vehicle;

use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

/**
 * Import all msrps by requesting the pledge-store upgrade api endpoint
 */
class ImportMsrp implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const RSI_TOKEN = 'STAR-CITIZEN.WIKI_DE_API_REQUEST';

    private CookieJar $cookieJar;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->cookieJar = new CookieJar;

        $client = Http::withOptions([
            'base_uri' => config('services.rsi_url'),
            'cookies' => $this->cookieJar,
        ])
            ->timeout(60)
            ->withHeaders([
                'X-RSI-Token' => self::RSI_TOKEN,
            ]);

        $query = <<<'QUERY'
{
    ships {
      id
      name
      msrp
      link
      skus {
        id
        title
        available
        price
      }
    }
}
QUERY;

        try {
            $client->post('https://robertsspaceindustries.com/api/account/v2/setAuthToken')->throw();
            $client->post('https://robertsspaceindustries.com/api/ship-upgrades/setContextToken')->throw();
            $response = $client->post(
                'https://robertsspaceindustries.com/pledge-store/api/upgrade',
                [
                    'query' => $query,
                ]
            )->throw();
        } catch (RequestException $e) {
            app('Log')::critical('Could not connect to RSI Pledge Store API', [
                'message' => $e->getMessage(),
            ]);

            $this->fail($e);

            return;
        }

        collect($response->json('data.ships', []))
            ->each(
                function (array $vehicle) {
                    /** @var Vehicle $model */
                    $model = Vehicle::query()->where('cig_id', $vehicle['id'])->first();

                    if ($model === null) {
                        return;
                    }

                    if ($vehicle['msrp'] !== null) {
                        $model->update(
                            [
                                'msrp' => substr((string) $vehicle['msrp'], 0, -2),
                                'pledge_url' => $vehicle['link'],
                            ]
                        );
                    }

                    if (! empty($vehicle['skus'])) {
                        collect($vehicle['skus'])->each(function (array $sku) use ($model) {
                            $model->skus()->updateOrCreate([
                                'cig_id' => $sku['id'],
                            ], [
                                'title' => $sku['title'],
                                'price' => substr((string) $sku['price'], 0, -2),
                                'available' => $sku['available'],
                            ]);
                        });
                    }
                }
            );
    }
}
