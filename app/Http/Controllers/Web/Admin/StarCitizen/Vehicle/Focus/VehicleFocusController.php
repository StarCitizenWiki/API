<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Focus;

use App\Http\Controllers\Controller;
use App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus;
use App\Models\System\Language;
use Illuminate\Http\Request;

/**
 * Class VehicleFocusController
 */
class VehicleFocusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view('admin.starcitizen.vehicles.foci.index')
            ->with(
                'foci',
                VehicleFocus::all()
            )
            ->with(
                'languages',
                Language::all()
            );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus $focus
     *
     * @return \Illuminate\Http\Response
     */
    public function show(VehicleFocus $focus)
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view('admin.starcitizen.vehicles.foci.show')->with(
            'focus',
            $focus
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request                               $request
     * @param \App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus $focus
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, VehicleFocus $focus)
    {
        return redirect()->route('web.admin.starcitizen.vehicle.focus.index');
    }
}
