<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\StarCitizen\Vehicle\Ship;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\TranslationRequest;
use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use Dingo\Api\Dispatcher;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Class ShipsController
 */
class ShipController extends Controller
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
        $this->middleware('auth')->except(['index']);
        $this->api = $dispatcher;
        $this->api->be(Auth::user());
    }

    /**
     * @return View
     */
    public function index(): View
    {
        $ships = $this->api->get('api/ships', ['limit' => 0]);

        return view(
            'user.starcitizen.vehicles.ships.index',
            [
                'ships' => $ships,
            ]
        );
    }

    /**
     * Display Ship data, edit Translations
     *
     * @param Ship $ship
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function edit(Ship $ship): View
    {
        $this->authorize('web.user.starcitizen.vehicles.update');

        /** @var Collection $changelogs */
        $changelogs = $ship->changelogs;

        $changelogs = $changelogs->merge($ship->translationChangelogs);

        $changelogs = $changelogs->sortByDesc('created_at');

        return view(
            'user.starcitizen.vehicles.ships.edit',
            [
                'ship' => $ship,
                'componentGroups' => $ship->componentsByClass(),
                'changelogs' => $changelogs,
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TranslationRequest $request
     * @param Ship               $ship
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(TranslationRequest $request, Ship $ship): RedirectResponse
    {
        $this->authorize('web.user.starcitizen.vehicles.update');

        $data = $request->validated();

        foreach ($data as $localeCode => $translation) {
            $ship->translations()->updateOrCreate(
                ['locale_code' => $localeCode],
                ['translation' => $translation]
            );
        }

        return redirect()->route('web.user.starcitizen.vehicles.ships.edit', $ship->getRouteKey())->withMessages(
            [
                'success' => [
                    __('crud.updated', ['type' => __('Schiff')]),
                ],
            ]
        );
    }
}
