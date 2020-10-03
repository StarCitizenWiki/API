<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\StarCitizen\ProductionStatus;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\TranslationRequest;
use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus;
use App\Models\System\Language;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function index(): View
    {
        $this->authorize('web.user.translations.view');

        return view(
            'user.starcitizen.production_statuses.index',
            [
                'translations' => ProductionStatus::all(),
                'languages' => Language::all(),
                'editRoute' => 'web.user.starcitizen.production-statuses.edit',
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param ProductionStatus $productionStatus
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function edit(ProductionStatus $productionStatus): View
    {
        $this->authorize('web.user.translations.update');

        return view(
            'user.starcitizen.production_statuses.edit',
            [
                'translation' => $productionStatus,
                'updateRoute' => 'web.user.starcitizen.production-statuses.update',
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TranslationRequest $request
     * @param ProductionStatus   $productionStatus
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(TranslationRequest $request, ProductionStatus $productionStatus): RedirectResponse
    {
        $this->authorize('web.user.translations.update');
        $data = $request->validated();

        foreach ($data as $localeCode => $translation) {
            $productionStatus->translations()->updateOrCreate(
                ['locale_code' => $localeCode],
                ['translation' => $translation]
            );
        }

        return redirect()->route('web.user.starcitizen.production-statuses.index')->withMessages(
            [
                'success' => [
                    __('crud.updated', ['type' => __('Produktionsstatus')]),
                ],
            ]
        );
    }
}
