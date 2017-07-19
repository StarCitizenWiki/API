<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DownloadStarCitizenDBShips;
use App\Traits\ProfilesMethodsTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

/**
 * Class ShipsController
 * @package App\Http\Controllers\Auth\Admin
 */
class ShipsController extends Controller
{
    use ProfilesMethodsTrait;

    /**
     * ShipsController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * @return View
     */
    public function showShipsView() : View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.ships.index')->with(
            'ships',
            File::allFiles(config('filesystems.disks.scdb_ships_splitted.root'))
        );
    }

    /**
     * @return RedirectResponse
     */
    public function downloadShips() : RedirectResponse
    {
        $this->startProfiling(__FUNCTION__);

        $this->dispatch(new DownloadStarCitizenDBShips());

        $this->stopProfiling(__FUNCTION__);

        return redirect()->back()->with(
            'success',
            ['Ships Download Queued']
        );
    }
}
