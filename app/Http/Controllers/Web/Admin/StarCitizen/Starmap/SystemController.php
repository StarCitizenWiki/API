<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DownloadStarmapData;
use App\Models\Starmap\CelestialObject;
use App\Models\Starmap\Starsystem;
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
    public function showStarmapSystemsView(): View
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