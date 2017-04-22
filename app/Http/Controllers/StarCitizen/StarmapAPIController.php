<?php
/**
 * User: Hannes
 * Date: 11.03.2017
 * Time: 20:07
 */

namespace App\Http\Controllers\StarCitizen;

use App\Exceptions\InvalidDataException;
use App\Http\Controllers\Controller;
use App\Repositories\StarCitizen\APIv1\Starmap\StarmapRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class StarmapAPIController
 *
 * @package App\Http\Controllers\StarCitizen
 */
class StarmapAPIController extends Controller
{
    /**
     * StarmapRepository
     *
     * @var StarmapRepository
     */
    private $repository;

    /**
     * StarmapAPIController constructor.
     *
     * @param StarmapRepository $repository StarmapRepository
     */
    public function __construct(StarmapRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Requests the given System Name
     *
     * @param String $name SystemName
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getSystem(String $name)
    {
        $name = strtoupper($name);

        Log::debug('Starmap System requested', [
            'method' => __METHOD__,
            'name' => $name,
        ]);

        try {
            return response()->json(
                $this->repository->getSystem($name)->asArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Returns a list with all known Systems
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getSystemList(Request $request)
    {
        Log::debug('Starmap System List requested', [
            'method' => __METHOD__,
        ]);

        $this->repository->getSystemList();
        $this->repository->transformer->addFilters($request);
        try {
            return response()->json(
                $this->repository->asArray(),
                200,
                [],
                JSON_PRETTY_PRINT
            );
        } catch (InvalidDataException $e) {
            return $e->getMessage();
        }
    }

	/**
	 * Requests the given System Name Astreoid belts
	 *
	 * @param String $name SystemName
	 *
	 * @return \Illuminate\Http\JsonResponse|string
	 */
	public function getAsteroidbelts(String $name)
	{
		$name = strtoupper($name);

		Log::debug('Starmap System Astreoidbelts requested', [
			'method' => __METHOD__,
			'name' => $name,
		]);

		try {
			return response()->json(
				$this->repository->getAsteroidbelts($name)->asArray(),
				200,
				[],
				JSON_PRETTY_PRINT
			);
		} catch (InvalidDataException $e) {
			return $e->getMessage();
		}
	}
}
