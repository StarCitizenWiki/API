<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\StarCitizen\Vehicle\GroundVehicle;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\TranslationRequest;
use App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle;
use Dingo\Api\Dispatcher;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Class GroundVehicleController
 */
class GroundVehicleController extends Controller
{
    /**
     * @var Dispatcher
     */
    private Dispatcher $api;

    /**
     * ShipsController constructor.
     *
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        parent::__construct();
        $this->middleware('auth');
        $this->api = $dispatcher;
        $this->api->be(Auth::user());
    }

    /**
     * @return View
     *
     * @throws AuthorizationException
     */
    public function index(): View
    {
        $this->authorize('web.user.starcitizen.vehicles.view');

        $vehicles = $this->api->get('api/vehicles', ['limit' => 0]);

        return view(
            'user.starcitizen.vehicles.ground_vehicles.index',
            [
                'groundVehicles' => $vehicles,
            ]
        );
    }

    /**
     * Display Ship data, edit Translations
     *
     * @param GroundVehicle $groundVehicle
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function edit(GroundVehicle $groundVehicle): View
    {
        $this->authorize('web.user.starcitizen.vehicles.update');

        $groundVehicle->load('components');

        /** @var Collection $changelogs */
        $changelogs = $groundVehicle->changelogs;

        $changelogs = $changelogs->merge($groundVehicle->translationChangelogs);

        $changelogs = $changelogs->sortByDesc('created_at');

        return view(
            'user.starcitizen.vehicles.ground_vehicles.edit',
            [
                'groundVehicle' => $groundVehicle,
                'componentGroups' => $groundVehicle->componentsByClass(),
                'changelogs' => $changelogs,
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TranslationRequest $request
     * @param GroundVehicle      $groundVehicle
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(TranslationRequest $request, GroundVehicle $groundVehicle): RedirectResponse
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
