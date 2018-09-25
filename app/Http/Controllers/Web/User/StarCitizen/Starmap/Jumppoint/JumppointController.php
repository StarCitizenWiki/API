<?php declare(strict_types = 1);
/**
 * User: Keonie
 * Date: 03.08.2017 16:44
 */

namespace App\Http\Controllers\Web\User\StarCitizen\Starmap\Jumppoint;

use App\Http\Controllers\Controller;
use App\Models\Api\StarCitizen\Starmap\Jumppoint\Jumppoint;
use Illuminate\Contracts\View\View;

/**
 * Class JumppointTunnelController
 */
class JumppointController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): View
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'user.starmap.jumppointtunnels.index',
            [
                'jumppointtunnels' => Jumppoint::orderBy('cig_id')->get(),
            ]
        );
    }
}
