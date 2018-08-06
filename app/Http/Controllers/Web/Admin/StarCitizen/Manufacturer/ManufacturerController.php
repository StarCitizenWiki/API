<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\StarCitizen\Manufacturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManufacturerTranslationRequest;
use App\Models\Api\StarCitizen\Manufacturer\Manufacturer;
use App\Models\System\Language;
use Illuminate\Contracts\View\View;

/**
 * Class ManufacturerController
 */
class ManufacturerController extends Controller
{
    /**
     * ManufacturerController constructor.
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
        $this->authorize('web.admin.starcitizen.manufacturers.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.starcitizen.manufacturers.index',
            [
                'manufacturers' => Manufacturer::all(),
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Api\StarCitizen\Manufacturer\Manufacturer $manufacturer
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Manufacturer $manufacturer)
    {
        $this->authorize('web.admin.starcitizen.manufacturers.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.starcitizen.manufacturers.edit',
            [
                'manufacturer' => $manufacturer,
                'updateRoute' => 'web.admin.starcitizen.manufacturers.update',
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\ManufacturerTranslationRequest     $request
     * @param \App\Models\Api\StarCitizen\Manufacturer\Manufacturer $manufacturer
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(ManufacturerTranslationRequest $request, Manufacturer $manufacturer)
    {
        $this->authorize('web.admin.starcitizen.manufacturers.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $data = $request->validated();
        $localeCodes = Language::all('locale_code')->keyBy('locale_code');

        foreach ($localeCodes as $localeCode => $model) {
            $manufacturer->translations()->updateOrCreate(
                [
                    'locale_code' => $localeCode,
                ],
                [
                    'description' => $data["description_{$localeCode}"],
                    'known_for' => $data["known_for_{$localeCode}"],
                ]
            );
        }

        return redirect()->route('web.admin.starcitizen.manufacturers.edit', $manufacturer->id);
    }
}
