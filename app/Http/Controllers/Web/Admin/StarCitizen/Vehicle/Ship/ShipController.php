<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Ship;

use App\Http\Controllers\Controller;
use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Request;

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

        return view('admin.starcitizen.vehicles.ships.index')->with(
            'ships',
            Ship::all()
        );
    }

    /**
     * Display Ship data, edit Translations
     *
     * @param  \App\Models\Api\StarCitizen\Vehicle\Ship\Ship $ship
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Ship $ship)
    {
        return view('admin.starcitizen.vehicles.ships.show')->with(
            'ship',
            $ship
        );
    }

    public function update(Request $request, Ship $ship)
    {

    }
}
