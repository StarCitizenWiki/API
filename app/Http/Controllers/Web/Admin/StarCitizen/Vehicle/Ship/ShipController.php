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
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): View
    {
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
     */
    public function edit(Ship $ship)
    {
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
     * @param \App\Http\Requests\TranslationRequest         $request
     * @param \App\Models\Api\StarCitizen\Vehicle\Ship $ship
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(TranslationRequest $request, Ship $ship)
    {
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
