<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\StarCitizen\Vehicle\GroundVehicle;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\TranslationRequest;
use App\Models\StarCitizen\Vehicle\GroundVehicle\GroundVehicle;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;

/**
 * Class GroundVehicleController
 */
class GroundVehicleController extends Controller
{
    /**
     * Ground Vehicle Controller constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth')->except(['index']);
    }

    /**
     * @return View
     */
    public function index(): View
    {
        return view(
            'web.starcitizen.vehicles.ground_vehicles.index',
            [
                'groundVehicles' => GroundVehicle::all(),
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
        $this->authorize('web.starcitizen.vehicles.update');

        $groundVehicle->load('components');

        /** @var Collection $changelogs */
        $changelogs = $groundVehicle->changelogs;

        $changelogs = $changelogs->merge($groundVehicle->translationChangelogs);

        $changelogs = $changelogs->sortByDesc('created_at');

        return view(
            'web.starcitizen.vehicles.ground_vehicles.edit',
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
        $this->authorize('web.starcitizen.vehicles.update');
        $data = $request->validated();

        foreach ($data as $localeCode => $translation) {
            $groundVehicle->translations()->updateOrCreate(
                ['locale_code' => $localeCode],
                ['translation' => $translation]
            );
        }

        return redirect()->route(
            'web.starcitizen.vehicles.ground-vehicles.edit',
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
