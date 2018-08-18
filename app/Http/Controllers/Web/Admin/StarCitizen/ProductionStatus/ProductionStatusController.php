<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\StarCitizen\ProductionStatus;

use App\Http\Controllers\Controller;
use App\Http\Requests\TranslationRequest;
use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus;
use App\Models\System\Language;

/**
 * Class ProductionStatus
 */
class ProductionStatusController extends Controller
{
    /**
     * ProductionStatusController constructor.
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
        $this->authorize('web.admin.starcitizen.translations.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.starcitizen.production_statuses.index',
            [
                'translations' => ProductionStatus::all(),
                'languages' => Language::all(),
                'editRoute' => 'web.admin.starcitizen.production_statuses.edit',
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus $status
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(ProductionStatus $status)
    {
        $this->authorize('web.admin.starcitizen.translations.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.starcitizen.production_statuses.edit',
            [
                'translation' => $status,
                'updateRoute' => 'web.admin.starcitizen.production_statuses.update',
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\TranslationRequest                         $request
     * @param \App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus $status
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(TranslationRequest $request, ProductionStatus $status)
    {
        $this->authorize('web.admin.starcitizen.translations.update');
        $data = $request->validated();

        foreach ($data as $localeCode => $translation) {
            $status->translations()->updateOrCreate(
                ['locale_code' => $localeCode],
                ['translation' => $translation]
            );
        }

        return redirect()->route('web.admin.starcitizen.production_statuses.index')->withMessages(
            [
                'success' => [
                    __('crud.updated', ['type' => __('Produktionsstatus')]),
                ],
            ]
        );
    }
}
