<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User\StarCitizen\Manufacturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StarCitizen\Manufacturer\ManufacturerTranslationRequest;
use App\Models\System\Language;
use Dingo\Api\Dispatcher;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * Class ManufacturerController
 */
class ManufacturerController extends Controller
{
    private const MANUFACTURER_PERMISSION = 'web.user.starcitizen.manufacturers.update';

    /**
     * @var \Dingo\Api\Dispatcher
     */
    protected $api;

    /**
     * ManufacturerController constructor.
     *
     * @param \Dingo\Api\Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        parent::__construct();
        $this->middleware('auth');
        $this->api = $dispatcher;
        $this->api->be(Auth::user());
    }

    /**
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): View
    {
        $this->authorize('web.user.starcitizen.manufacturers.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $manufacturers = $this->api->with(
            [
                'limit' => 0,
            ]
        )->get("api/manufacturers");

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
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(string $manufacturer)
    {
        $this->authorize(self::MANUFACTURER_PERMISSION);
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $manufacturer = $this->api->get("api/manufacturers/{$manufacturer}");

        return view(
            'user.starcitizen.manufacturers.edit',
            [
                'manufacturer' => $manufacturer,
                'updateRoute' => self::MANUFACTURER_PERMISSION,
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\StarCitizen\Manufacturer\ManufacturerTranslationRequest $request
     * @param string                                                                     $manufacturer
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(ManufacturerTranslationRequest $request, string $manufacturer)
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
