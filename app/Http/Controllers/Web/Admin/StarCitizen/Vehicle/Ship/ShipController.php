<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Ship;

use App\Http\Controllers\Controller;
use App\Http\Requests\TranslationRequest;
use Dingo\Api\Dispatcher;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * Class ShipsController
 */
class ShipController extends Controller
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

        $ships = $this->api->with(
            [
                'limit' => 0,
            ]
        )->get('api/vehicles/ships');

        return view(
            'admin.starcitizen.vehicles.ships.index',
            [
                'ships' => $ships,
            ]
        );
    }

    /**
     * Display Ship data, edit Translations
     *
     * @param string $ship
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(string $ship)
    {
        $this->authorize('web.admin.starcitizen.vehicles.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $ship = $this->api->get("api/vehicles/ships/{$ship}");

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
     * @param \App\Http\Requests\TranslationRequest $request
     * @param string                                $ship
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(TranslationRequest $request, string $ship)
    {
        $this->authorize('web.admin.starcitizen.vehicles.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $data = $request->validated();
        $ship = $this->api->get("api/vehicles/ships/{$ship}");

        foreach ($data as $localeCode => $translation) {
            $ship->translations()->updateOrCreate(
                ['locale_code' => $localeCode],
                ['translation' => $translation]
            );
        }

        return redirect()->route('web.admin.starcitizen.vehicles.ships.edit', $ship->getRouteKey());
    }
}
