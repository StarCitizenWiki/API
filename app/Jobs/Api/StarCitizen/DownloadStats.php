<?php declare(strict_types = 1);

namespace App\Jobs\Api\StarCitizen;

use App\Exceptions\InvalidDataException;
use App\Jobs\AbstractBaseDownloadData;
use App\Models\Api\StarCitizen\Stat;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\Psr7\str;

/**
 * Class DownloadStats
 */
class DownloadStats extends AbstractBaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const STATS_ENDPOINT = '/api/stats/getCrowdfundStats';
    private const FIELD_FUNDS = 'funds';
    private const FIELD_FLEET = 'fleet';
    private const FIELD_FANS = 'fans';

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('Log')::info('Starting '.__CLASS__.' Job.');
        $this->initClient();

        try {
            $response = $this->makeRequest();
        } catch (RequestException | ClientException | ServerException $e) {
            app('Log')::error(
                'Could not connect to RSI, aborting',
                [
                    'message' => str($e->getRequest()),
                    'code' => $e->getResponse()->getStatusCode() ?? 0,
                ]
            );

            return;
        }

        try {
            $data = $this->parseBody($response);
        } catch (InvalidDataException $e) {
            app('Log')::error('Provided data is missing needed keys.');

            return;
        }

        Stat::create(
            [
                self::FIELD_FUNDS => substr($data['data'][self::FIELD_FUNDS], 0, -2),
                self::FIELD_FLEET => $data['data'][self::FIELD_FLEET],
                self::FIELD_FANS => $data['data'][self::FIELD_FANS],
            ]
        );

        app('Log')::info('Job '.__CLASS__.' finished.');
    }

    /**
     * Requests Fans, Funds, Fleet from RSI
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function makeRequest(): ResponseInterface
    {
        return $this->client->post(
            self::STATS_ENDPOINT,
            [
                'json' => [
                    self::FIELD_FANS => true,
                    self::FIELD_FLEET => true,
                    self::FIELD_FUNDS => true,
                ],
            ]
        );
    }

    /**
     * Parses the response body into an array
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return array
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    private function parseBody(ResponseInterface $response): array
    {
        $data = [];

        try {
            $data = json_decode((string) $response->getBody(), true);
        } catch (InvalidArgumentException $e) {
            app('Log')::error(
                'Downloading Stats failed. Body does not contain valid Data!',
                [
                    'body' => (string) $response->getBody(),
                    'status' => $response->getStatusCode(),
                ]
            );
        }

        if (array_diff_key(array_flip([self::FIELD_FUNDS, self::FIELD_FANS, self::FIELD_FLEET]), $data['data'])) {
            throw new InvalidDataException();
        }

        return $data;
    }
}
