<?php
/**
 * User: Keonie
 * Date: 07.08.2018 14:13
 */

namespace App\Http\Controllers\Api\V1\StarCitizen\Starmap;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Api\StarCitizen\Starmap\Jumppoint\Jumppoint;
use App\Transformers\Api\V1\StarCitizen\Starmap\JumppointTransformer;
use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Illuminate\Http\Request;

/**
 * Class JumppointController
 * @package App\Http\Controllers\Api\V1\StarCitizen\Starmap
 */
class JumppointController extends ApiController
{
    /**
     * @var \App\Transformers\Api\V1\StarCitizen\Starmap\JumppointTransformer
     */
    private $transformer;

    /**
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * JumppointController constructor.
     *
     * @param \App\Transformers\Api\V1\StarCitizen\Starmap\JumppointTransformer $transformer
     * @param \Illuminate\Http\Request                                          $request
     */
    public function __construct(JumppointTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;
        $this->request = $request;

        if ($request->has('locale')) {
            $this->transformer->setLocale($request->get('locale'));
        }
    }

    /**
     * @param $id
     *
     * @return \Dingo\Api\Http\Response
     */
    public function show($id)
    {
        try {
            $jumppoint = Jumppoint::where('cig_id', $id)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf('No Jumppoint found for Query: %s', $id));
        }
        return $this->response->item($jumppoint, $this->transformer);
    }

    /**
     * @param String $starsystemName
     *
     * @return \Dingo\Api\Http\Response
     */
    public function showByStarsystem(String $starsystemName)
    {
        //TODO prüfen ob paginator so funktioniert (ggf. noch get())
        $starsystem = Starsystem::where('code', $starsystemName)->paginator(100);
        if (is_null($starsystem)) {
            throw new InvalidArgumentException("Starsystem {$starsystemName} not found!");
        }

        $jumppoints = Jumppoint::where('entry_cig_system_id', $starsystem->cig_id)
            ->orWhere('exit_cig_system_id', $starsystem->cig_id)
            ->groupBy('cig_id')
            ->get()
            ->toArray();
        return $this->response->item($jumppoints, $this->transformer);
    }

    /**
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        $jumppoints = Jumppoint::paginate();
        return $this->response->paginator($jumppoints, $this->transformer);
    }
}