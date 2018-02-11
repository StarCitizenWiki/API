<?php declare(strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DownloadStarCitizenDBShips;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;

/**
 * Class ShipsController
 */
class ShipController extends Controller
{
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
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \App\Exceptions\WrongMethodNameException
     */
    public function showShipsView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.ships.index')->with(
            'ships',
            File::allFiles(config('filesystems.disks.scdb_ships_splitted.root'))
        );
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function downloadShips(): RedirectResponse
    {
        $this->dispatch(new DownloadStarCitizenDBShips());

        return redirect()->back()->with(
            'success',
            ['Ships Download Queued']
        );
    }
}
