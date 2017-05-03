<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DownloadStarCitizenDBShips;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Class ShipsController
 * @package App\Http\Controllers\Auth\Admin
 */
class ShipsController extends Controller
{
    /**
     * @return View
     */
    public function showShipsView() : View
    {
        $this->logger->debug('Ships View requested');

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
        $this->dispatch(new DownloadStarCitizenDBShips());

        return redirect()->back()->with(
            'success',
            ['Ships Download Queued']
        );
    }
}
