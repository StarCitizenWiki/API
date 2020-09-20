<?php declare(strict_types=1);
/**
 * User: Keonie
 * Date: 07.08.2018 14:31
 */

namespace App\Http\Controllers\Api\V1\StarCitizen\Starmap\CelestialObject;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject;
use App\Transformers\Api\V1\StarCitizen\Starmap\CelestialObjectTransformer;
use Dingo\Api\Contract\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class CelestialObjectController
 */
class CelestialObjectController extends ApiController
{
    /**
     * CelestialObjectController constructor.
     *
     * @param \Illuminate\Http\Request                                                $request
     * @param \App\Transformers\Api\V1\StarCitizen\Starmap\CelestialObjectTransformer $transformer
     */
    public function __construct(Request $request, CelestialObjectTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * @param String $code
     *
     * @return \Dingo\Api\Http\Response
     */
    public function show(string $code)
    {
        $code = urldecode($code);

        try {
            /** @var \App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject $celestialObject */
            $celestialObject = CelestialObject::where('code', $code)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $code));
        }

        return $this->getResponse($celestialObject);
    }

    //TODO weitere Funktionen

    /**
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        return $this->getResponse(CelestialObject::query());
    }

    /**
     * Search Endpoint
     *
     * @return \Dingo\Api\Http\Response
     */
    public function search()
    {
        $query = $this->request->get('query', '');
        $query = urldecode($query);
        $queryBuilder = CelestialObject::where('name', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($queryBuilder);
    }
}
