<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\StarCitizen\ProductionNote;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\TranslationRequest;
use App\Models\Api\StarCitizen\ProductionNote\ProductionNote;
use App\Models\System\Language;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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
            'user.starcitizen.production_notes.index',
            [
                'translations' => ProductionNote::all(),
                'languages' => Language::all(),
                'editRoute' => 'web.user.starcitizen.production-notes.edit',
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param ProductionNote $note
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function edit(ProductionNote $note): View
    {
        $this->authorize('web.user.translations.update');

        return view(
            'user.starcitizen.production_notes.edit',
            [
                'translation' => $note,
                'updateRoute' => 'web.user.starcitizen.production-notes.update',
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TranslationRequest $request
     * @param ProductionNote     $note
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(TranslationRequest $request, ProductionNote $note): RedirectResponse
    {
        $this->authorize('web.user.translations.update');

        $data = $request->validated();

        foreach ($data as $localeCode => $translation) {
            $note->translations()->updateOrCreate(
                ['locale_code' => $localeCode],
                ['translation' => $translation]
            );
        }

        return redirect()->route('web.user.starcitizen.production-notes.index')->withMessages(
            [
                'success' => [
                    __('crud.updated', ['type' => __('Produktionsnotiz')]),
                ],
            ]
        );
    }
}
