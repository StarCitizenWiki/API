<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User\StarCitizen\Vehicle\Type;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\TranslationRequest;
use App\Models\Api\StarCitizen\Vehicle\Type\Type;
use App\Models\System\Language;

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
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('web.user.translations.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

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
     * @param \App\Models\Api\StarCitizen\Vehicle\Type\Type $type
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Type $type)
    {
        $this->authorize('web.user.translations.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

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
     * @param \App\Http\Requests\System\TranslationRequest         $request
     * @param \App\Models\Api\StarCitizen\Vehicle\Type\Type $type
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(TranslationRequest $request, Type $type)
    {
        $this->authorize('web.user.translations.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

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
