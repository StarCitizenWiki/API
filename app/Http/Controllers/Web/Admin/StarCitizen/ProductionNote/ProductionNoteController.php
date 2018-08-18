<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\StarCitizen\ProductionNote;

use App\Http\Controllers\Controller;
use App\Http\Requests\TranslationRequest;
use App\Models\Api\StarCitizen\ProductionNote\ProductionNote;
use App\Models\System\Language;

/**
 * Class ProductionNote
 */
class ProductionNoteController extends Controller
{
    /**
     * ProductionNoteController constructor.
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
            'admin.starcitizen.production_notes.index',
            [
                'translations' => ProductionNote::all(),
                'languages' => Language::all(),
                'editRoute' => 'web.admin.starcitizen.production_notes.edit',
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Api\StarCitizen\ProductionNote\ProductionNote $note
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(ProductionNote $note)
    {
        $this->authorize('web.admin.starcitizen.translations.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.starcitizen.production_notes.edit',
            [
                'translation' => $note,
                'updateRoute' => 'web.admin.starcitizen.production_notes.update',
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\TranslationRequest                     $request
     * @param \App\Models\Api\StarCitizen\ProductionNote\ProductionNote $note
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(TranslationRequest $request, ProductionNote $note)
    {
        $this->authorize('web.admin.starcitizen.translations.update');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $data = $request->validated();

        foreach ($data as $localeCode => $translation) {
            $note->translations()->updateOrCreate(
                ['locale_code' => $localeCode],
                ['translation' => $translation]
            );
        }

        return redirect()->route('web.admin.starcitizen.production_notes.index')->withMessages(
            [
                'success' => [
                    __('crud.updated', ['type' => __('Produktionsnotiz')]),
                ],
            ]
        );
    }
}
