<?php declare(strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DownloadStarCitizenDBShips;
use App\Models\StarCitizen\Manufacturer\Manufacturer;
use App\Models\StarCitizen\Vehicle\Ship\Ship;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;

/**
 * Class ManufacturerController
 */
class ManufacturerController extends Controller
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
    public function showManufacturersView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.manufacturers.index')->with(
            'manufacturers',
            Manufacturer::all()
        );
    }
}
