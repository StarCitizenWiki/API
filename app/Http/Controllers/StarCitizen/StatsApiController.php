<?php declare(strict_types = 1);

namespace App\Http\Controllers\StarCitizen;

use App\Exceptions\InvalidDataException;
use App\Http\Controllers\Controller;
use App\Repositories\StarCitizen\Interfaces\StatsRepositoryInterface;
use App\Traits\CachesResponseTrait as CachesResponse;

/**
 * Class StatsAPIController
 *
 * @package App\Http\Controllers\StarCitizen
 */
class StatsApiController extends Controller
{
    use CachesResponse;

    /**
     * StatsRepository
     *
     * @var \App\Repositories\StarCitizen\ApiV1\StatsRepository
     */
    private $repository;

    /**
     * StatsAPIController constructor.
     *
     * @param \App\Repositories\StarCitizen\Interfaces\StatsRepositoryInterface $repository
     */
    public function __construct(StatsRepositoryInterface $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * Returns just the Funds
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function getFunds()
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return $this->getJsonPrettyPrintResponse(__FUNCTION__);
    }

    /**
     * Returns just the Fleet
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function getFleet()
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return $this->getJsonPrettyPrintResponse(__FUNCTION__);
    }

    /**
     * Returns just the Fans
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function getFans()
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return $this->getJsonPrettyPrintResponse(__FUNCTION__);
    }

    /**
     * Returns all
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getAll()
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return $this->getJsonPrettyPrintResponse(__FUNCTION__);
    }

    /**
     * Returns just Funds from last hours
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function getLastHoursFunds()
    {
        return $this->getJsonPrettyPrintResponse("lastHours");
    }

    /**
     * Returns just Funds from last days
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function getLastDaysFunds()
    {
        return $this->getJsonPrettyPrintResponse("lastDays");
    }

    /**
     * Returns just Funds from last weeks
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function getLastWeeksFunds()
    {
        return $this->getJsonPrettyPrintResponse("lastWeeks");
    }

    /**
     * Returns just Funds from last months
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function getLastMonthsFunds()
    {
        return $this->getJsonPrettyPrintResponse("lastMonths");
    }

    /**
     * Wrapper for all get Calls
     *
     * @param \Closure | string $func Function to call
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    private function getJsonPrettyPrintResponse($func)
    {
        if ($this->isCached()) {
            return $this->getCachedResponse();
        }

        try {
            $this->repository->$func();
            $data = $this->repository->toArray();

            return $this->jsonResponse($data);
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }
}
