<?php declare(strict_types=1);

namespace App\Http\Controllers\Web\User\StarCitizen\Starmap\Starsystem;

use App\Http\Controllers\Controller;
use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;
use Illuminate\Contracts\View\View;

/**
 * Class AdminStarmapController
 */
class StarsystemController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): View
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'user.starmap.systems.list',
            [
                'systems' => Starsystem::orderBy('code')->get(),
            ]
        );
    }
}
