<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 13:35
 */

namespace App\Repositories\StarCitizen\APIv1\Stats;

use App\Exceptions\ResponseNotRequestedException;
use App\Repositories\StarCitizen\APIv1\BaseStarCitizenAPI as BaseStarCitizenAPI;
use GuzzleHttp\Psr7\Response;

class StatsRepository implements StatsInterface
{

    private $_getFans = true;
    private $_getFleet = true;
    private $_getFunds = true;
    private $_chartType = 'hour';

    private $_api;
    /** @var  Response */
    private $_response;

    function __construct(BaseStarCitizenAPI $api)
    {
        $this->_api = $api;
    }

    /**
     * https://robertsspaceindustries.com/api/stats/getCrowdfundStats
     * @return StatsRepository
     *
     */
    public function getCrowdfundStats() : StatsRepository
    {
        $this->_api->request('POST', 'stats/getCrowdfundStats', [
            'json' => [
                'chart' => $this->_chartType,
                'fans' => $this->_getFans,
                'fleet' => $this->_getFleet,
                'funds' => $this->_getFunds
            ]
        ]);

        $this->_saveResponse();

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
        $this->_checkIfResponseRequested();
        return $this->_response->getBody();
    }

    public function asArray() : array
    {
        $this->_checkIfResponseRequested();
        return json_decode((string) $this->_response->getBody(), true);
    }

    public function asResponse() : Response
    {
        $this->_checkIfResponseRequested();
        return $this->_response;
    }

    private function _saveResponse()
    {
        $this->_response = $this->_api->getResponse();
    }

    private function _checkIfResponseRequested()
    {
        if ($this->_response === null) {
            throw new ResponseNotRequestedException('You need to request a response first');
        }
    }
}