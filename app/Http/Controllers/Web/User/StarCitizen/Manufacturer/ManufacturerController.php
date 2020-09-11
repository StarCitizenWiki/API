<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User\StarCitizen\Manufacturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StarCitizen\Manufacturer\ManufacturerTranslationRequest;
use App\Models\System\Language;
use Dingo\Api\Dispatcher;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Class ManufacturerController
 */
class ManufacturerController extends Controller
{
    private const MANUFACTURER_PERMISSION = 'web.user.starcitizen.manufacturers.update';

    /**
     * @var Dispatcher
     */
    protected Dispatcher $api;

    /**
     * ManufacturerController constructor.
     *
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        parent::__construct();
        $this->middleware('auth');
        $this->api = $dispatcher;
        $this->api->be(Auth::user());
    }

    /**
     * @return View
     *
     * @throws AuthorizationException
     */
    public function index(): View
    {
        $this->authorize('web.user.starcitizen.manufacturers.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $manufacturers = $this->api->get('api/manufacturers', ['limit' => 0]);

        return view(
            'user.starcitizen.manufacturers.index',
            [
                'manufacturers' => $manufacturers,
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
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $manufacturer = $this->api->get("api/manufacturers/{$manufacturer}");

        return view(
            'user.starcitizen.manufacturers.edit',
            [
                'manufacturer' => $manufacturer,
                'updateRoute' => self::MANUFACTURER_PERMISSION,
                'changelogs' => $manufacturer->changelogs,
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
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $data = $request->validated();
        $manufacturer = $this->api->get("api/manufacturers/{$manufacturer}");

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
            'web.user.starcitizen.manufacturers.edit',
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
