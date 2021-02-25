<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\StarCitizen\Vehicle\Type;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\TranslationRequest;
use App\Models\StarCitizen\Vehicle\Type\Type;
use App\Models\System\Language;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Class VehicleTypeController
 */
class TypeController extends Controller
{
    /**
     * VehicleTypeController constructor.
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
            'user.starcitizen.vehicles.types.index',
            [
                'translations' => Type::all(),
                'languages' => Language::all(),
                'editRoute' => 'web.user.starcitizen.vehicles.types.edit',
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param Type $type
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function edit(Type $type): View
    {
        $this->authorize('web.user.translations.update');

        return view(
            'user.starcitizen.vehicles.types.edit',
            [
                'translation' => $type,
                'updateRoute' => 'web.user.starcitizen.vehicles.types.update',
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TranslationRequest $request
     * @param Type               $type
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(TranslationRequest $request, Type $type): RedirectResponse
    {
        $this->authorize('web.user.translations.update');

        $data = $request->validated();

        foreach ($data as $localeCode => $translation) {
            $type->translations()->updateOrCreate(
                ['locale_code' => $localeCode],
                ['translation' => $translation]
            );
        }

        return redirect()->route('web.user.starcitizen.vehicles.types.index')->withMessages(
            [
                'success' => [
                    __('crud.updated', ['type' => __('Fahrzeugtyp')]),
                ],
            ]
        );
    }
}
