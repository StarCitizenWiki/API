<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\StarCitizen\Manufacturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManufacturerTranslationRequest;
use App\Models\System\Language;
use Dingo\Api\Dispatcher;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * Class ManufacturerController
 */
class ManufacturerController extends Controller
{
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
        $this->middleware('auth:admin');
        $this->api = $dispatcher;
        $this->api->be(Auth::guard('admin')->user());
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

        $manufacturers = $this->api->with(
            [
                'limit' => 0,
            ]
        )->get("api/manufacturers");

        return view(
            'admin.starcitizen.manufacturers.index',
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
        $this->authorize('web.admin.starcitizen.manufacturers.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $manufacturer = $this->api->get("api/manufacturers/{$manufacturer}");

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
     * @param \App\Http\Requests\ManufacturerTranslationRequest $request
     * @param string                                            $manufacturer
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(ManufacturerTranslationRequest $request, string $manufacturer)
    {
        $this->authorize('web.admin.starcitizen.manufacturers.update');
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

        return redirect()->route('web.admin.starcitizen.manufacturers.edit', $manufacturer->id);
    }
}
