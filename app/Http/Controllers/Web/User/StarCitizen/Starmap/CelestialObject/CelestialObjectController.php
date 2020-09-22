<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\StarCitizen\Starmap\CelestialObject;

use App\Http\Controllers\Controller;
use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject;
use Illuminate\View\View;

class CelestialObjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        return view(
            'user.starmap.celestialobjects.list',
            [
                'celestialobjects' => CelestialObject::query()->orderBy('code')->get(),
            ]
        );
    }
}
