<?php
/**
 * User: Hannes
 * Date: 19.01.2017
 * Time: 13:35
 */

namespace App\Repositories\StarCitizen\APIv1\Stats;

use App\Repositories\StarCitizen\APIv1\BaseStarCitizenAPI as BaseStarCitizenAPI;
use App\Transformers\StarCitizen\Stats\FansTransformer;
use App\Transformers\StarCitizen\Stats\FleetTransformer;
use App\Transformers\StarCitizen\Stats\FundsTransformer;
use App\Transformers\StarCitizen\Stats\StatsTransformer;

class StatsRepository extends BaseStarCitizenAPI implements StatsInterface
{
    private $_getFans = true;
    private $_getFleet = true;
    private $_getFunds = true;
    private $_chartType = 'hour';

    /**
     * https://robertsspaceindustries.com/api/stats/getCrowdfundStats
     * @return StatsRepository
     */
    public function getCrowdfundStats() : StatsRepository
    {
        $this->request('POST', 'stats/getCrowdfundStats', $this->getRequestBody());
        return $this;
    }

	private function getRequestBody() : array
	{
		$requestContent = [
		    'chart' => $this->_chartType
        ];

		if ($this->_getFans) {
			$requestContent = array_merge($requestContent, ['fans' => $this->_getFans]);
		}

		if ($this->_getFleet) {
			$requestContent = array_merge($requestContent, ['fleet' => $this->_getFleet]);
		}

		if ($this->_getFunds) {
			$requestContent = array_merge($requestContent, ['funds' => $this->_getFunds]);
		}

		$requestBody = [
		    'json' => $requestContent
        ];

		return $requestBody;
	}

    public function getFunds() : StatsRepository
    {
	    $this->_getFans = false;
	    $this->_getFleet = false;
        $this->withTransformer(FundsTransformer::class);
        return $this->getCrowdfundStats();
    }

    public function getFans() : StatsRepository
    {
	    $this->_getFleet = false;
	    $this->_getFunds = false;
        $this->withTransformer(FansTransformer::class);
        return $this->getCrowdfundStats();
    }

    public function getFleet() : StatsRepository
    {
	    $this->_getFans = false;
	    $this->_getFunds = false;
        $this->withTransformer(FleetTransformer::class);
        return $this->getCrowdfundStats();
    }

    public function getAll() : StatsRepository
    {
        $this->withTransformer(StatsTransformer::class);
        return $this->getCrowdfundStats();
    }

    /**
     * Sets the Chart Type to 'hour'
     * @return StatsRepository
     */
    public function lastHours() : StatsRepository
    {
        $this->_chartType = 'hour';
	    return $this->getAll();
    }

    /**
     * Sets the Chart Type to 'day'
     * @return StatsRepository
     */
    public function lastDays() : StatsRepository
    {
        $this->_chartType = 'day';
	    return $this->getAll();
    }

    /**
     * Sets the Chart Type to 'week'
     * @return StatsRepository
     */
    public function lastWeeks() : StatsRepository
    {
        $this->_chartType = 'week';
	    return $this->getAll();
    }

    /**
     * Sets the Chart Type to 'month'
     * @return StatsRepository
     */
    public function lastMonths() : StatsRepository
    {
        $this->_chartType = 'month';
	    return $this->getAll();
    }

}