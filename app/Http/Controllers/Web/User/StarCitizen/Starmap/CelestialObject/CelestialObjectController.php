<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\StarCitizen\Starmap\CelestialObject;

use App\Http\Controllers\Controller;
use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject;
use Illuminate\Contracts\View\View;

/**
 * Class AdminStarmapController
 */
class CelestialObjectController extends Controller
{
    public function index(): View
    {
        $this->authorize('web.user.starcitizen.starmap.view');

        return view(
            'user.starcitizen.starmap.celestial_objects.index',
            [
                'objects' => CelestialObject::query()->with('starsystem')->orderBy('code')->get(),
            ]
        );
    }
}
