<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\StarCitizen\Vehicle\Size;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\TranslationRequest;
use App\Models\StarCitizen\Vehicle\Size\Size;
use App\Models\System\Language;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Class VehicleSizeController
 */
class SizeController extends Controller
{
    /**
     * VehicleSizeController constructor.
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
        $this->authorize('web.translations.view');

        return view(
            'web.starcitizen.vehicles.sizes.index',
            [
                'translations' => Size::all(),
                'languages' => Language::all(),
                'editRoute' => 'web.starcitizen.vehicles.sizes.edit',
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param Size $size
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function edit(Size $size): View
    {
        $this->authorize('web.translations.update');

        return view(
            'web.starcitizen.vehicles.sizes.edit',
            [
                'translation' => $size,
                'updateRoute' => 'web.starcitizen.vehicles.sizes.update',
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TranslationRequest $request
     * @param Size               $size
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(TranslationRequest $request, Size $size): RedirectResponse
    {
        $this->authorize('web.translations.update');

        $data = $request->validated();

        foreach ($data as $localeCode => $translation) {
            $size->translations()->updateOrCreate(
                ['locale_code' => $localeCode],
                ['translation' => $translation]
            );
        }

        return redirect()->route('web.starcitizen.vehicles.sizes.index')->withMessages(
            [
                'success' => [
                    __('crud.updated', ['type' => __('Fahrzeuggröße')]),
                ],
            ]
        );
    }
}
