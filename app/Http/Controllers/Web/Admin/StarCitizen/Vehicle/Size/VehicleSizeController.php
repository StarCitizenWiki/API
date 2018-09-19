<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Size;

use App\Http\Controllers\Controller;
use App\Http\Requests\TranslationRequest;
use App\Models\Api\StarCitizen\Vehicle\Size\VehicleSize;
use App\Models\System\Language;

/**
 * Class VehicleSizeController
 */
class VehicleSizeController extends Controller
{
    /**
     * VehicleSizeController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:admin');
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
        $this->authorize('web.admin.translations.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.starcitizen.vehicles.sizes.index',
            [
                'translations' => VehicleSize::all(),
                'languages' => Language::all(),
                'editRoute' => 'web.admin.starcitizen.vehicles.sizes.edit',
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Api\StarCitizen\Vehicle\Size\VehicleSize $size
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(VehicleSize $size)
    {
        $this->authorize('web.admin.translations.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.starcitizen.vehicles.sizes.edit',
            [
                'translation' => $size,
                'updateRoute' => 'web.admin.starcitizen.vehicles.sizes.update',
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\TranslationRequest                $request
     * @param \App\Models\Api\StarCitizen\Vehicle\Size\VehicleSize $size
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(TranslationRequest $request, VehicleSize $size)
    {
        $this->authorize('web.admin.translations.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $data = $request->validated();

        foreach ($data as $localeCode => $translation) {
            $size->translations()->updateOrCreate(
                ['locale_code' => $localeCode],
                ['translation' => $translation]
            );
        }

        return redirect()->route('web.admin.starcitizen.vehicles.sizes.index')->withMessages(
            [
                'success' => [
                    __('crud.updated', ['type' => __('Fahrzeuggröße')]),
                ],
            ]
        );
    }
}
