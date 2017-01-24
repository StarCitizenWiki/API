<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 13:35
 */

namespace App\Repositories\StarCitizen\APIv1\Stats;

use App\Exceptions\EmptyResponseException;
use App\Repositories\StarCitizen\APIv1\BaseStarCitizenAPI as BaseStarCitizenAPI;
use GuzzleHttp\Psr7\Response;

class StatsRepository extends BaseStarCitizenAPI implements StatsInterface
{

    private $_getFans = true;
    private $_getFleet = true;
    private $_chartType = 'hour';

    /** @var  Response */
    private $_response;

    function __construct()
    {
        parent::__construct();
    }

    /**
     * https://robertsspaceindustries.com/api/stats/getCrowdfundStats
     * @return \GuzzleHttp\Psr7\Response
     *
     */
    public function getCrowdfundStats() : StatsRepository
    {
        $response = $this->_connection->request('POST', 'stats/getCrowdfundStats', [
            'json' => [
                'chart' => $this->_chartType,
                'fans' => $this->_getFans,
                'fleet' => $this->_getFleet
            ]
        ]);

        $this->_response = $response;

        return $this;
    }

    /**
     * Sets the Chart Type to 'hour'
     * @return StatsRepository
     */
    public function lastHours() : StatsRepository
    {
        $this->_chartType = 'hour';
        return $this;
    }

    /**
     * Sets the Chart Type to 'day'
     * @return StatsRepository
     */
    public function lastDays() : StatsRepository
    {
        $this->_chartType = 'day';
        return $this;
    }

    /**
     * Sets the Chart Type to 'week'
     * @return StatsRepository
     */
    public function lastWeeks() : StatsRepository
    {
        $this->_chartType = 'week';
        return $this;
    }

    /**
     * Sets the Chart Type to 'month'
     * @return StatsRepository
     */
    public function lastMonths() : StatsRepository
    {
        $this->_chartType = 'month';
        return $this;
    }

    public function asJSON() : String
    {
        $this->_checkIfResponseIsEmpty();
        return $this->_response->getBody()->getContents();
    }

    public function asArray() : array
    {
        $this->_checkIfResponseIsEmpty();
        return json_decode($this->_response->getBody()->getContents(), true);
    }

    public function asResponse() : Response
    {
        $this->_checkIfResponseIsEmpty();
        return $this->_response;
    }

    private function _checkIfResponseIsEmpty()
    {
        if ($this->_response === null) {
            throw new EmptyResponseException();
        }
    }
}