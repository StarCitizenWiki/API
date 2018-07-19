<?php declare(strict_types = 1);
/**
 * User: Keonie
 * Date: 03.08.2017 16:44
 */

namespace App\Http\Controllers\Auth\Admin\StarCitizen\Starmap;

use App\Http\Controllers\Controller;
use App\Jobs\StarCitizen\Starmap\DownloadJumppointTunnel;
use App\Models\Api\StarCitizen\Starmap\Jumppoint;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Class JumppointTunnelController
 */
class JumppointTunnelController extends Controller
{

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function showJumppointTunnelView(): View
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view('admin.starmap.jumppointtunnels.index')->with(
            'jumppointtunnels',
            Jumppoint::orderBy('cig_id')->get()
        );
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function downloadJumppointTunnels(): RedirectResponse
    {
        $this->dispatch(new DownloadJumppointTunnel());

        return redirect()->back()->with(
            'success',
            ['Jumppoint Tunnel Download Queued']
        );
    }
}