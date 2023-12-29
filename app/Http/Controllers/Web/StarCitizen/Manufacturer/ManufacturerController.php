<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\StarCitizen\Manufacturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StarCitizen\Manufacturer\ManufacturerTranslationRequest;
use App\Models\StarCitizen\Manufacturer\Manufacturer;
use App\Models\System\Language;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Class ManufacturerController
 */
class ManufacturerController extends Controller
{
    private const MANUFACTURER_PERMISSION = 'web.starcitizen.manufacturers.update';

    /**
     * ManufacturerController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth')->except('index');
    }

    /**
     * @return View
     */
    public function index(): View
    {
        return view(
            'web.starcitizen.manufacturers.index',
            [
                'manufacturers' => Manufacturer::all(),
                'manufacturers_ingame' => \App\Models\SC\Manufacturer::query()->groupBy('name')->orderBy('name')->get(),
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param string $manufacturer
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function edit(string $manufacturer): View
    {
        $this->authorize(self::MANUFACTURER_PERMISSION);

        $manufacturer = Manufacturer::query()
            ->where('name', $manufacturer)
            ->orWhere('name_short', $manufacturer)
            ->firstOrFail();

        return view(
            'web.starcitizen.manufacturers.edit',
            [
                'manufacturer' => $manufacturer,
                'updateRoute' => self::MANUFACTURER_PERMISSION,
                'changelogs' => $manufacturer->changelogs
                    ->merge($manufacturer->translationChangelogs)
                    ->sortByDesc('created_at'),
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ManufacturerTranslationRequest $request
     * @param string                         $manufacturer
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(ManufacturerTranslationRequest $request, string $manufacturer): RedirectResponse
    {
        $this->authorize(self::MANUFACTURER_PERMISSION);

        $data = $request->validated();
        $manufacturer = Manufacturer::query()
            ->where('name', $manufacturer)
            ->orWhere('name_short', $manufacturer)
            ->firstOrFail();

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

        return redirect()->route(
            'web.starcitizen.manufacturers.edit',
            $manufacturer->getRouteKey()
        )->withMessages(
            [
                'success' => [
                    __('crud.updated', ['type' => __('Hersteller')]),
                ],
            ]
        );
    }
}
