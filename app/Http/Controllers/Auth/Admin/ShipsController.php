<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Jobs\DownloadStarCitizenDBShips;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
        Log::debug('Ships View requested', [
            'method' => __METHOD__,
        ]);

        return view('admin.ships.index')->with(
            'ships',
            File::allFiles(config('filesystems.disks.scdb_ships.root'))
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
