<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User\StarCitizen\Vehicle\GroundVehicle;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\TranslationRequest;
use App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\VehicleTranslation;
use App\Models\System\ModelChangelog;
use Illuminate\Contracts\View\View;

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
        $this->middleware('auth');
    }

    /**
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): View
    {
        $this->authorize('web.user.starcitizen.vehicles.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'user.starcitizen.vehicles.ground_vehicles.index',
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
        $this->authorize('web.user.starcitizen.vehicles.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        /** @var \Illuminate\Support\Collection $changelog */
        $changelog = $groundVehicle->changelogs;
        $groundVehicle->translations->each(
            function (VehicleTranslation $translation) use (&$changelog) {
                $translation->changelogs->each(
                    function (ModelChangelog $transChange) use (&$changelog) {
                        $changelog->push($transChange);
                    }
                );
            }
        );

        $changelog = $changelog->sortByDesc('created_at');

        return view(
            'user.starcitizen.vehicles.ground_vehicles.edit',
            [
                'groundVehicle' => $groundVehicle,
                'changelogs' => $changelog,
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\System\TranslationRequest                    $request
     * @param \App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle $groundVehicle
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(TranslationRequest $request, GroundVehicle $groundVehicle)
    {
        $this->authorize('web.user.starcitizen.vehicles.update');
        $data = $request->validated();

        foreach ($data as $localeCode => $translation) {
            $groundVehicle->translations()->updateOrCreate(
                ['locale_code' => $localeCode],
                ['translation' => $translation]
            );
        }

        return redirect()->route(
            'web.user.starcitizen.vehicles.ground-vehicles.edit',
            $groundVehicle->getRouteKey()
        )->withMessages(
            [
                'success' => [
                    __('crud.updated', ['type' => __('Fahrzeug')]),
                ],
            ]
        );
    }
}
