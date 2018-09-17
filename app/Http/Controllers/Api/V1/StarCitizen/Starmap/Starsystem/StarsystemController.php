<?php
/**
 * User: Keonie
 * Date: 07.08.2018 14:14
 */

namespace App\Http\Controllers\Api\V1\StarCitizen\Starmap\Starsystem;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;
use App\Transformers\Api\V1\StarCitizen\Starmap\StarsystemTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

/**
 * Class StarsystemController
 * @package App\Http\Controllers\Api\V1\StarCitizen\Starmap
 */
class StarsystemController extends ApiController
{

    /**
     * @var \App\Transformers\Api\V1\StarCitizen\Starmap\StarsystemTransformer
     */
    protected $transformer;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * StarsystemController constructor.
     *
     * @param \App\Transformers\Api\V1\StarCitizen\Starmap\StarsystemTransformer $transformer
     * @param \Illuminate\Http\Request                                           $request
     */
    public function __construct(StarsystemTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;
        $this->request = $request;

        parent::__construct($request);
    }

    /**
     * @param String $starsystemName
     *
     * @return \Dingo\Api\Http\Response
     */
    public function show(String $starsystemName)
    {
        $starsystemName = urldecode($starsystemName);
        try {
            $starsystem = Starsystem::where('code', $starsystemName)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf('No Starsystem found for Query: %s', $starsystemName));
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