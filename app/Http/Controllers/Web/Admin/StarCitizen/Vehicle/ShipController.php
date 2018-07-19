<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\StarCitizen\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use Illuminate\Contracts\View\View;

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
     */
    public function index(): View
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view('admin.ships.index')->with(
            'ships',
            Ship::all()
        );
    }
}
