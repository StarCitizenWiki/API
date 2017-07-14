<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 13:35
 */

namespace App\Repositories\StarCitizen\APIv1;

use App\Facades\Log;
use App\Repositories\StarCitizen\BaseStarCitizenAPI;
use App\Repositories\StarCitizen\Interfaces\StatsInterface;
use App\Transformers\StarCitizen\Stats\FansTransformer;
use App\Transformers\StarCitizen\Stats\FleetTransformer;
use App\Transformers\StarCitizen\Stats\FundsTransformer;
use App\Transformers\StarCitizen\Stats\StatsTransformer;

/**
 * Class StatsRepository
 *
 * @package App\Repositories\StarCitizen\APIv1\Stats
 */
class StatsRepository extends BaseStarCitizenAPI implements StatsInterface
{
    private $getFans = true;
    private $getFleet = true;
    private $getFunds = true;
    private $chartType = 'hour';

    /**
     * Reads the Crowdfunding Stats from RSI
     * https://robertsspaceindustries.com/api/stats/getCrowdfundStats
     *
     * @return StatsRepository
     */
    public function getCrowdfundStats() : StatsRepository
    {
        $requestBody = $this->getRequestBody();

        Log::debug('Requesting CrowdfundStats', [
            'request_body' => $requestBody,
        ]);

        $this->request(
            'POST',
            'stats/getCrowdfundStats',
            $requestBody
        );

        return $this;
    }

    /**
     * Requests only funds
     *
     * @return StatsRepository
     */
    public function getFunds() : StatsRepository
    {
        Log::debug('Getting Fund Stats');
        $this->getFans = false;
        $this->getFleet = false;
        $this->withTransformer(FundsTransformer::class);

        return $this->getCrowdfundStats();
    }

    /**
     * Requests only fans
     *
     * @return StatsRepository
     */
    public function getFans() : StatsRepository
    {
        Log::debug('Getting Fans Stats');
        $this->getFleet = false;
        $this->getFunds = false;
        $this->withTransformer(FansTransformer::class);

        return $this->getCrowdfundStats();
    }

    /**
     * Requests only fleet
     *
     * @return StatsRepository
     */
    public function getFleet() : StatsRepository
    {
        Log::debug('Getting Fleet Stats');
        $this->getFans = false;
        $this->getFunds = false;
        $this->withTransformer(FleetTransformer::class);

        return $this->getCrowdfundStats();
    }

    /**
     * Requests all stats
     *
     * @return StatsRepository
     */
    public function getAll() : StatsRepository
    {
        Log::debug('Getting All Stats');
        $this->withTransformer(StatsTransformer::class);

        return $this->getCrowdfundStats();
    }

    /**
     * Sets the Chart Type to 'hour'
     *
     * @return StatsRepository
     */
    public function lastHours() : StatsRepository
    {
        Log::debug('Getting Stats for last Hours');
        $this->chartType = 'hour';

        return $this->getAll();
    }

    /**
     * Sets the Chart Type to 'day'
     *
     * @return StatsRepository
     */
    public function lastDays() : StatsRepository
    {
        Log::debug('Getting Stats for last Days');
        $this->chartType = 'day';

        return $this->getAll();
    }

    /**
     * Sets the Chart Type to 'week'
     *
     * @return StatsRepository
     */
    public function lastWeeks() : StatsRepository
    {
        Log::debug('Getting Stats for last Weeks');
        $this->chartType = 'week';

        return $this->getAll();
    }

    /**
     * Sets the Chart Type to 'month'
     *
     * @return StatsRepository
     */
    public function lastMonths() : StatsRepository
    {
        Log::debug('Getting Stats for last Months');
        $this->chartType = 'month';

        return $this->getAll();
    }

    /**
     * Prepares the request body
     *
     * @return array
     */
    private function getRequestBody() : array
    {
        Log::debug('Starting Request Body Assembly');
        $requestContent = [
            'chart' => $this->chartType,
        ];

        if ($this->getFans) {
            $requestContent = array_merge($requestContent, [
                'fans' => $this->getFans,
            ]);
        }

        if ($this->getFleet) {
            $requestContent = array_merge($requestContent, [
                'fleet' => $this->getFleet,
            ]);
        }

        if ($this->getFunds) {
            $requestContent = array_merge($requestContent, [
                'funds' => $this->getFunds,
            ]);
        }

        $requestBody = [
            'json' => $requestContent,
        ];

        Log::debug('Finished assembling Request Body');

        return $requestBody;
    }
}
