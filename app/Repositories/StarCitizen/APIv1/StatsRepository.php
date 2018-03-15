<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 13:35
 */

namespace App\Repositories\StarCitizen\ApiV1;

use App\Exceptions\InvalidDataException;
use App\Repositories\StarCitizen\AbstractStarCitizenRepository as StarCitizenRepository;
use App\Repositories\StarCitizen\Interfaces\Stats\StatsRepositoryInterface;
use App\Transformers\StarCitizen\Stats\FansTransformer;
use App\Transformers\StarCitizen\Stats\FleetTransformer;
use App\Transformers\StarCitizen\Stats\FundsTransformer;
use App\Transformers\StarCitizen\Stats\StatsTransformer;
use Spatie\Fractal\Fractal;

/**
 * Class StatsRepository
 */
class StatsRepository extends StarCitizenRepository implements StatsRepositoryInterface
{
    /**
     * @var array Request Body
     */
    private $requestBody = [];

    /**
     * Requests only funds
     *
     * @return \Spatie\Fractal\Fractal
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    public function getFunds(): Fractal
    {
        $this->manager->transformWith(FundsTransformer::class);
        $this->requestBody = [
            'funds' => true,
        ];

        return $this->requestStats();
    }

    /**
     * Requests only fans
     *
     * @return \Spatie\Fractal\Fractal
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    public function getFans(): Fractal
    {
        $this->manager->transformWith(FansTransformer::class);
        $this->requestBody = [
            'fans' => true,
        ];

        return $this->requestStats();
    }

    /**
     * Requests only fleet
     *
     * @return \Spatie\Fractal\Fractal
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    public function getFleet(): Fractal
    {
        $this->manager->transformWith(FleetTransformer::class);
        $this->requestBody = [
            'fleet' => true,
        ];

        return $this->requestStats();
    }

    /**
     * Returns all Crowdfund Stats
     * https://robertsspaceindustries.com/api/stats/getCrowdfundStats
     *
     * @return \Spatie\Fractal\Fractal
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    public function getAll()
    {
        $this->manager->transformWith(StatsTransformer::class);
        $this->requestBody = [
            'fleet' => true,
            'funds' => true,
            'fans' => true,
        ];

        return $this->requestStats();
    }

    /**
     * Reads the Crowdfunding Stats from RSI
     * https://robertsspaceindustries.com/api/stats/getCrowdfundStats
     *
     * @return \Spatie\Fractal\Fractal
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    private function requestStats(): Fractal
    {
        app('Log')::info('Requesting CrowdfundStats', ['request_body' => $this->requestBody]);

        $data = $this->client->post(
            '/api/stats/getCrowdfundStats',
            [
                'json' => $this->requestBody,
            ]
        );

        if (!$this->checkIfResponseDataIsValid($data)) {
            throw new InvalidDataException("Response data does not meet validity check");
        }

        $data = json_decode((string) $data->getBody(), true);

        return $this->manager->item($data);
    }
}
