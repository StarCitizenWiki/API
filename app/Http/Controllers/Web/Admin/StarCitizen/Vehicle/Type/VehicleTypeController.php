<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Type;

use App\Http\Controllers\Controller;
use App\Models\Api\StarCitizen\Vehicle\Type\VehicleType;
use App\Models\System\Language;
use Illuminate\Http\Request;

/**
 * Class VehicleTypeController
 */
class VehicleTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view('admin.starcitizen.vehicles.types.index')
            ->with(
                'types',
                VehicleType::all()
            )
            ->with(
                'languages',
                Language::all()
            );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Api\StarCitizen\Vehicle\Type\VehicleType $type
     *
     * @return \Illuminate\Http\Response
     */
    public function show(VehicleType $type)
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view('admin.starcitizen.vehicles.types.show')->with(
            'type',
            $type
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }
}
