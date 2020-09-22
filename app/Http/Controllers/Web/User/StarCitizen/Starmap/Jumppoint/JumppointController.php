<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\StarCitizen\Starmap\Jumppoint;

use App\Http\Controllers\Controller;
use App\Models\Api\StarCitizen\Starmap\Jumppoint\Jumppoint;
use Illuminate\View\View;

/**
 * Class JumppointTunnelController
 */
class JumppointController extends Controller
{
    /**
     * @return View
     */
    public function index(): View
    {
        return view(
            'user.starmap.jumppointtunnels.index',
            [
                'jumppointtunnels' => Jumppoint::query()->orderBy('cig_id')->get(),
            ]
        );
    }
}
