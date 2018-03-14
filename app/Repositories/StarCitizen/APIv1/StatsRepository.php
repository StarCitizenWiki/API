<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 13:35
 */

namespace App\Repositories\StarCitizen\ApiV1;

use App\Exceptions\InvalidDataException;
use App\Repositories\StarCitizen\AbstractStarCitizenRepository as StarCitizenRepository;
use App\Repositories\StarCitizen\Interfaces\StatsRepositoryInterface;
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
    private $getFans = true;
    private $getFleet = true;
    private $getFunds = true;
    private $chartType = 'hour';

    /**
     * Requests only funds
     *
     * @return \Spatie\Fractal\Fractal
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    public function getFunds(): Fractal
    {
        $this->getFans = false;
        $this->getFleet = false;
        $this->manager->transformWith(FundsTransformer::class);

        $data = $this->getCrowdfundStats();

        return $this->manager->item($data);
    }

    /**
     * Reads the Crowdfunding Stats from RSI
     * https://robertsspaceindustries.com/api/stats/getCrowdfundStats
     *
     * @return array
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    private function getCrowdfundStats(): array
    {
        $requestBody = $this->getRequestBody();

        app('Log')::info('Requesting CrowdfundStats', ['request_body' => $requestBody]);

        $data = $this->client->post(
            '/api/stats/getCrowdfundStats',
            $requestBody
        );

        if (!$this->checkIfResponseDataIsValid($data)) {
            throw new InvalidDataException("Response does not meet validity check");
        }

        return json_decode((string) $data->getBody(), true);
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
        $this->getFleet = false;
        $this->getFunds = false;
        $this->manager->transformWith(FansTransformer::class);

        $data = $this->getCrowdfundStats();

        return $this->manager->item($data);
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
        $this->getFleet = false;
        $this->getFunds = false;
        $this->manager->transformWith(FleetTransformer::class);

        $data = $this->getCrowdfundStats();

        return $this->manager->item($data);
    }

    /**
     * Sets the Chart Type to 'hour'
     *
     * @return \App\Repositories\StarCitizen\ApiV1\StatsRepository
     *
     * @throws \App\Exceptions\WrongMethodNameException
     * @throws \App\Exceptions\InvalidDataException
     */
    public function lastHours(): StatsRepository
    {
        app('Log')::info(make_name_readable(__FUNCTION__));
        $this->chartType = 'hour';

        return $this->getAll();
    }

    /**
     * Requests all stats
     *
     * @return \Spatie\Fractal\Fractal
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    public function getAll(): Fractal
    {
        $this->manager->transformWith(StatsTransformer::class);

        $data = $this->getCrowdfundStats();

        return $this->manager->item($data);
    }

    /**
     * Sets the Chart Type to 'day'
     *
     * @return \App\Repositories\StarCitizen\ApiV1\StatsRepository
     *
     * @throws \App\Exceptions\InvalidDataException
     * @throws \App\Exceptions\WrongMethodNameException
     */
    public function lastDays(): StatsRepository
    {
        app('Log')::info(make_name_readable(__FUNCTION__));
        $this->chartType = 'day';

        return $this->getAll();
    }

    /**
     * Sets the Chart Type to 'week'
     *
     * @return \App\Repositories\StarCitizen\ApiV1\StatsRepository
     *
     * @throws \App\Exceptions\InvalidDataException
     * @throws \App\Exceptions\WrongMethodNameException
     */
    public function lastWeeks(): StatsRepository
    {
        app('Log')::info(make_name_readable(__FUNCTION__));
        $this->chartType = 'week';

        return $this->getAll();
    }

    /**
     * Sets the Chart Type to 'month'
     *
     * @return \App\Repositories\StarCitizen\ApiV1\StatsRepository
     *
     * @throws \App\Exceptions\InvalidDataException
     * @throws \App\Exceptions\WrongMethodNameException
     */
    public function lastMonths(): StatsRepository
    {
        app('Log')::info(make_name_readable(__FUNCTION__));
        $this->chartType = 'month';

        return $this->getAll();
    }

    /**
     * Prepares the request body
     *
     * @return array
     */
    private function getRequestBody(): array
    {
        $requestContent = [
            'chart' => $this->chartType,
        ];

        if ($this->getFans) {
            $requestContent = array_merge(
                $requestContent,
                [
                    'fans' => $this->getFans,
                ]
            );
        }

        if ($this->getFleet) {
            $requestContent = array_merge(
                $requestContent,
                [
                    'fleet' => $this->getFleet,
                ]
            );
        }

        if ($this->getFunds) {
            $requestContent = array_merge(
                $requestContent,
                [
                    'funds' => $this->getFunds,
                ]
            );
        }

        return [
            'json' => $requestContent,
        ];
    }
}
