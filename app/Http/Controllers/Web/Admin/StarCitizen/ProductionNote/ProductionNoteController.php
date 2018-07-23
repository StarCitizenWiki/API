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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.starcitizen.production_notes.index',
            [
                'translations' => ProductionNote::all(),
                'languages' => Language::all(),
                'editRoute' => 'web.admin.starcitizen.production_notes.show',
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Api\StarCitizen\ProductionNote\ProductionNote $note
     *
     * @return \Illuminate\Http\Response
     */
    public function show(ProductionNote $note)
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.starcitizen.production_notes.show',
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
     */
    public function update(TranslationRequest $request, ProductionNote $note)
    {
        $data = $request->validated();

        foreach ($data as $localeCode => $translation) {
            $note->translations()->updateOrCreate(
                [
                    'locale_code' => $localeCode,
                    'translation' => $translation,
                ]
            );
        }

        return redirect()->route('web.admin.starcitizen.production_notes.index');
    }
}
