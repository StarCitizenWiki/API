<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Starmap\Jumppoint;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Api\StarCitizen\Starmap\Jumppoint\Jumppoint;
use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;
use App\Transformers\Api\V1\StarCitizen\Starmap\JumppointTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

/**
 * Class JumppointController
 */
class JumppointController extends ApiController
{
    /**
     * JumppointController constructor.
     *
     * @param Request              $request
     * @param JumppointTransformer $transformer
     */
    public function __construct(Request $request, JumppointTransformer $transformer)
    {
        $this->transformer = $transformer;

        parent::__construct($request);
    }

    /**
     * @param int $id
     *
     * @return Response
     */
    public function show($id): Response
    {
        try {
            /** @var Jumppoint $jumppoint */
            $jumppoint = Jumppoint::where('cig_id', $id)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $id));
        }

        return $this->getResponse($jumppoint);
    }

    /**
     * @param String $starsystemName
     *
     * @return Response
     */
    public function showByStarsystem(string $starsystemName): Response
    {
        //TODO prüfen ob paginator so funktioniert (ggf. noch get())
        $starsystem = Starsystem::where('code', $starsystemName)->paginator(100);
        if ($starsystem === null) {
            throw new InvalidArgumentException("Starsystem {$starsystemName} not found!");
        }

        $jumppoints = Jumppoint::where('entry_cig_system_id', $starsystem->cig_id)
            ->orWhere('exit_cig_system_id', $starsystem->cig_id)
            ->groupBy('cig_id')
            ->get()
            ->toArray();

        return $this->response->collection($jumppoints, $this->transformer);
    }

    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->getResponse(Jumppoint::query());
    }
}
