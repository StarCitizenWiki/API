<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Focus;

use App\Http\Controllers\Controller;
use App\Http\Requests\TranslationRequest;
use App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus;
use App\Models\System\Language;

/**
 * Class VehicleFocusController
 */
class VehicleFocusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.starcitizen.vehicles.foci.index',
            [
                'translations' => VehicleFocus::all(),
                'languages' => Language::all(),
                'editRoute' => 'web.admin.starcitizen.vehicles.foci.edit',
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus $focus
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(VehicleFocus $focus)
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.starcitizen.vehicles.foci.edit',
            [
                'translation' => $focus,
                'updateRoute' => 'web.admin.starcitizen.vehicles.foci.update',
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\TranslationRequest                  $request
     * @param \App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus $focus
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(TranslationRequest $request, VehicleFocus $focus)
    {
        $data = $request->validated();

        foreach ($data as $localeCode => $translation) {
            $focus->translations()->updateOrCreate(
                ['locale_code' => $localeCode],
                ['translation' => $translation]
            );
        }

        return redirect()->route('web.admin.starcitizen.vehicles.foci.index');
    }
}
