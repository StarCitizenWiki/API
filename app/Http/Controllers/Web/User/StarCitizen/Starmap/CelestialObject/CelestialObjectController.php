<?php declare(strict_types=1);

namespace App\Http\Controllers\Web\User\StarCitizen\Starmap\CelestialObject;

use App\Http\Controllers\Controller;
use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject;

class CelestialObjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'user.starmap.celestialobjects.list',
            [
                'celestialobjects' => CelestialObject::orderBy('code')->get(),
            ]
        );
    }
}
