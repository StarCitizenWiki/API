<?php declare(strict_types=1);
/**
 * User: Keonie
 * Date: 07.08.2018 14:14
 */

namespace App\Http\Controllers\Api\V1\StarCitizen\Starmap\Starsystem;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;
use App\Transformers\Api\V1\StarCitizen\Starmap\StarsystemTransformer;
use Dingo\Api\Contract\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class StarsystemController
 */
class StarsystemController extends ApiController
{
    /**
     * StarsystemController constructor.
     *
     * @param \Illuminate\Http\Request                                           $request
     * @param \App\Transformers\Api\V1\StarCitizen\Starmap\StarsystemTransformer $transformer
     */
    public function __construct(Request $request, StarsystemTransformer $transformer)
    {
        $this->transformer = $transformer;

        parent::__construct($request);
    }

    /**
     * @param String $starsystemName
     *
     * @return \Dingo\Api\Http\Response
     */
    public function show(string $starsystemName)
    {
        $starsystemName = urldecode($starsystemName);
        try {
            /** @var \App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem $starsystem */
            $starsystem = Starsystem::where('code', $starsystemName)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $starsystemName));
        }

        return $this->getResponse($starsystem);
    }

    /**
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        return $this->getResponse(Starsystem::query());
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
        $queryBuilder = Starsystem::where('name', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($queryBuilder);
    }
}
