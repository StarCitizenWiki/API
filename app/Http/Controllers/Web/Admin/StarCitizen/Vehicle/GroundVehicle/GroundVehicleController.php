<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\GroundVehicle;

use App\Http\Controllers\Controller;
use App\Http\Requests\TranslationRequest;
use App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle;
use Dingo\Api\Dispatcher;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * Class GroundVehicleController
 */
class GroundVehicleController extends Controller
{
    /**
     * ShipsController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:admin');
    }

    /**
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): View
    {
        $this->authorize('web.admin.starcitizen.vehicles.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.starcitizen.vehicles.ground_vehicles.index',
            [
                'groundVehicles' => GroundVehicle::all(),
            ]
        );
    }

    /**
     * Display Ship data, edit Translations
     *
     * @param \App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle $groundVehicle
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(GroundVehicle $groundVehicle)
    {
        $this->authorize('web.admin.starcitizen.vehicles.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.starcitizen.vehicles.ground_vehicles.edit',
            [
                'groundVehicle' => $groundVehicle,
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\TranslationRequest                           $request
     * @param \App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle $groundVehicle
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(TranslationRequest $request, GroundVehicle $groundVehicle)
    {
        $this->authorize('web.admin.starcitizen.vehicles.update');
        $data = $request->validated();

        foreach ($data as $localeCode => $translation) {
            $groundVehicle->translations()->updateOrCreate(
                ['locale_code' => $localeCode],
                ['translation' => $translation]
            );
        }

        return redirect()->route('web.admin.starcitizen.vehicles.ground-vehicles.edit', $groundVehicle->getRouteKey())->withMessages(
            [
                'success' => [
                    __('crud.updated', ['type' => __('Fahrzeug')]),
                ],
            ]
        );
    }
}
