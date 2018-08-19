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
     * @var \Dingo\Api\Dispatcher
     */
    protected $api;

    /**
     * ShipsController constructor.
     *
     * @param \Dingo\Api\Dispatcher $api
     */
    public function __construct(Dispatcher $api)
    {
        parent::__construct();
        $this->middleware('auth:admin');
        $this->api = $api;
        $this->api->be(Auth::guard('admin')->user());
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

        $groundVehicles = $this->api->with(
            [
                'limit' => 0,
            ]
        )->get('api/vehicles/ground_vehicles');


        return view(
            'admin.starcitizen.vehicles.ground_vehicles.index',
            [
                'ground_vehicles' => $groundVehicles,
            ]
        );
    }

    /**
     * Display Ship data, edit Translations
     *
     * @param string $groundVehicle
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(string $groundVehicle)
    {
        $this->authorize('web.admin.starcitizen.vehicles.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $groundVehicle = $this->api->get("api/vehicles/ground_vehicles/{$groundVehicle}");

        return view(
            'admin.starcitizen.vehicles.ground_vehicles.edit',
            [
                'ground_vehicle' => $groundVehicle,
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\TranslationRequest $request
     * @param string                                $groundVehicle
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(TranslationRequest $request, string $groundVehicle)
    {
        $this->authorize('web.admin.starcitizen.vehicles.update');
        $data = $request->validated();

        $groundVehicle = $this->api->get("api/vehicles/ground_vehicles/{$groundVehicle}");

        foreach ($data as $localeCode => $translation) {
            $groundVehicle->translations()->updateOrCreate(
                ['locale_code' => $localeCode],
                ['translation' => $translation]
            );
        }

        return redirect()->route('web.admin.starcitizen.vehicles.ground_vehicles.edit', $groundVehicle->getRouteKey())->withMessages(
            [
                'success' => [
                    __('crud.updated', ['type' => __('Fahrzeug')]),
                ],
            ]
        );
    }
}
