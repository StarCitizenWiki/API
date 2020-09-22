<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Starmap\Starsystem;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;
use App\Transformers\Api\V1\StarCitizen\Starmap\StarsystemTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class StarsystemController
 */
class StarsystemController extends ApiController
{
    /**
     * StarsystemController constructor.
     *
     * @param Request               $request
     * @param StarsystemTransformer $transformer
     */
    public function __construct(Request $request, StarsystemTransformer $transformer)
    {
        $this->transformer = $transformer;

        parent::__construct($request);
    }

    /**
     * @param String $starsystemName
     *
     * @return Response
     */
    public function show(string $starsystemName): Response
    {
        $starsystemName = urldecode($starsystemName);
        try {
            /** @var Starsystem $starsystem */
            $starsystem = Starsystem::where('code', $starsystemName)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $starsystemName));
        }

        return $this->getResponse($starsystem);
    }

    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->getResponse(Starsystem::query());
    }

    /**
     * Search Endpoint
     *
     * @return Response
     */
    public function search(): Response
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
