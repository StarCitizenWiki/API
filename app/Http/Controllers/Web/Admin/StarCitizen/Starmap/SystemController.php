<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\StarCitizen\Starmap;

use App\Http\Controllers\Controller;
use App\Jobs\StarCitizen\Starmap\DownloadStarmapData;
use App\Models\Api\StarCitizen\Starmap\CelestialObject;
use App\Models\Api\StarCitizen\Starmap\Starsystem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Class AdminStarmapController
 */
class SystemController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): View
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view('admin.starmap.systems.list')->with(
            'systems',
            Starsystem::orderBy('code')->get()
        );
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function showStarmapCelestialObjectView(): View
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view('admin.starmap.celestialobjects.list')->with(
            'celestialobjects',
            CelestialObject::orderBy('code')->get()
        );
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function downloadStarmap(): RedirectResponse
    {
        $this->dispatch(new DownloadStarmapData());

        return redirect()->back()->with(
            'success',
            ['Starmap Download Queued']
        );
    }
}
