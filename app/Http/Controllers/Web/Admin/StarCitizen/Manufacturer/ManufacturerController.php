<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\StarCitizen\Manufacturer;

use App\Http\Controllers\Controller;
use App\Models\Api\StarCitizen\Manufacturer\Manufacturer;
use Illuminate\Contracts\View\View;

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
     */
    public function index(): View
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view('admin.manufacturers.index')->with(
            'manufacturers',
            Manufacturer::all()
        );
    }
}
