<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Size;

use App\Http\Controllers\Controller;
use App\Models\Api\StarCitizen\Vehicle\Size\VehicleSize;
use App\Models\System\Language;
use Illuminate\Http\Request;

/**
 * Class VehicleSizeController
 */
class VehicleSizeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view('admin.starcitizen.vehicles.sizes.index')
            ->with(
                'sizes',
                VehicleSize::all()
            )
            ->with(
                'languages',
                Language::all()
            );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Api\StarCitizen\Vehicle\Size\VehicleSize $size
     *
     * @return \Illuminate\Http\Response
     */
    public function show(VehicleSize $size)
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view('admin.starcitizen.vehicles.sizes.show')->with(
            'size',
            $size
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VehicleSize $size)
    {
        //
    }
}
