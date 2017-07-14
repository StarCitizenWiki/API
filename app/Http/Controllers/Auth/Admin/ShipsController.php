<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Facades\Log;
use App\Http\Controllers\Controller;
use App\Jobs\DownloadStarCitizenDBShips;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
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
        Log::info(get_human_readable_name_from_view_function(__FUNCTION__), Auth::user()->getBasicInfoForLog());

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
        self::startExecutionTimer();

        $this->dispatch(new DownloadStarCitizenDBShips());

        self::endExecutionTimer();

        return redirect()->back()->with(
            'success',
            ['Ships Download Queued']
        );
    }
}
