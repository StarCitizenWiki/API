<?php
/**
 * User: Keonie
 * Date: 07.08.2018 14:14
 */

namespace App\Http\Controllers\Api\V1\StarCitizen\Starmap;

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
     * @var \App\Http\Controllers\Api\V1\StarCitizen\Starmap\Request
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

        if ($request->has('locale')) {
            $this->transformer->setLocale($request->get('locale'));
        }
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
            $starsystem = Starsystem::where('name', $starsystemName)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf('No Starsystem found for Query: %s', $starsystemName));
        }
        return $this->response->item($starsystem, $this->transformer);
    }

    /**
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        $starsystems = Starsystem::paginate();
        return $this->response->paginator($starsystems, $this->transformer);
    }
}