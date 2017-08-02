<?php declare(strict_types = 1);

namespace App\Http\Controllers\StarCitizen;

use App\Exceptions\InvalidDataException;
use App\Http\Controllers\Controller;
use App\Repositories\StarCitizen\APIv1\StatsRepository;
use App\Traits\ProfilesMethodsTrait;
use Illuminate\Http\Request;

/**
 * Class StatsAPIController
 *
 * @package App\Http\Controllers\StarCitizen
 */
class StatsAPIController extends Controller
{
    use ProfilesMethodsTrait;

    /**
     * StatsRepository
     *
     * @var \App\Repositories\StarCitizen\APIv1\StatsRepository
     */
    private $repository;

    private $request;

    /**
     * StatsAPIController constructor.
     *
     * @param \Illuminate\Http\Request                            $request
     * @param \App\Repositories\StarCitizen\APIv1\StatsRepository $repository StatsRepository
     */
    public function __construct(Request $request, StatsRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
        $this->request = $request;
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
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function getAll(Request $request)
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
        $this->startProfiling(__FUNCTION__);

        try {
            $this->addTrace("Calling Function {$func}", __FUNCTION__, __LINE__);
            $this->repository->$func();
            if (method_exists($this->repository->transformer, 'addFilters')) {
                $this->addTrace("Adding Filters", __FUNCTION__, __LINE__);
                $this->repository->transformer->addFilters($this->request);
            }
            $this->addTrace("Getting Data", __FUNCTION__, __LINE__);
            $data = $this->repository->asArray();

            $this->stopProfiling(__FUNCTION__);

            return response()->json(
                $data,
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            $this->addTrace("Getting Data failed with Message {$e->getMessage()}", __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return $e->getMessage();
        }
    }
}
