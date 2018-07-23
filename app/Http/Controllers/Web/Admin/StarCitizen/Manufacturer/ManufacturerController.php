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
            'admin.starcitizen.manufacturers.index',
            [
                'manufacturers' => Manufacturer::all(),
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Api\StarCitizen\ProductionNote\ProductionNote $manufacturer
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Manufacturer $manufacturer)
    {
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
     */
    public function update(ManufacturerTranslationRequest $request, Manufacturer $manufacturer)
    {
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
