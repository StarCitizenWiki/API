<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User\StarCitizen\Vehicle\Focus;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\TranslationRequest;
use App\Models\Api\StarCitizen\Vehicle\Focus\Focus;
use App\Models\System\Language;

/**
 * Class VehicleFocusController
 */
class FocusController extends Controller
{
    /**
     * VehicleFocusController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('web.user.translations.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'user.starcitizen.vehicles.foci.index',
            [
                'translations' => Focus::all(),
                'languages' => Language::all(),
                'editRoute' => 'web.user.starcitizen.vehicles.foci.edit',
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Api\StarCitizen\Vehicle\Focus\Focus $focus
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Focus $focus)
    {
        $this->authorize('web.user.translations.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'user.starcitizen.vehicles.foci.edit',
            [
                'translation' => $focus,
                'updateRoute' => 'web.user.starcitizen.vehicles.foci.update',
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\System\TranslationRequest    $request
     * @param \App\Models\Api\StarCitizen\Vehicle\Focus\Focus $focus
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(TranslationRequest $request, Focus $focus)
    {
        $this->authorize('web.user.translations.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $data = $request->validated();

        foreach ($data as $localeCode => $translation) {
            $focus->translations()->updateOrCreate(
                ['locale_code' => $localeCode],
                ['translation' => $translation]
            );
        }

        return redirect()->route('web.user.starcitizen.vehicles.foci.index')->withMessages(
            [
                'success' => [
                    __('crud.updated', ['type' => __('Fahrzeugfokus')]),
                ],
            ]
        );
    }
}
