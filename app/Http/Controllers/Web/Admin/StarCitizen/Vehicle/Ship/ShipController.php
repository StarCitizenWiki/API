<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Ship;

use App\Http\Controllers\Controller;
use App\Http\Requests\TranslationRequest;
use App\Models\Api\StarCitizen\Vehicle\Ship;
use Illuminate\Contracts\View\View;

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
            'admin.starcitizen.vehicles.ships.index',
            [
                'ships' => Ship::all(),
            ]
        );
    }

    /**
     * Display Ship data, edit Translations
     *
     * @param  \App\Models\Api\StarCitizen\Vehicle\Ship $ship
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Ship $ship)
    {
        $this->authorize('web.admin.starcitizen.vehicles.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.starcitizen.vehicles.ships.edit',
            [
                'ship' => $ship,
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\TranslationRequest    $request
     * @param \App\Models\Api\StarCitizen\Vehicle\Ship $ship
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(TranslationRequest $request, Ship $ship)
    {
        $this->authorize('web.admin.starcitizen.vehicles.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $data = $request->validated();

        foreach ($data as $localeCode => $translation) {
            $ship->translations()->updateOrCreate(
                ['locale_code' => $localeCode],
                ['translation' => $translation]
            );
        }

        return redirect()->route('web.admin.starcitizen.vehicles.ships.edit', $ship->getRouteKey());
    }
}
