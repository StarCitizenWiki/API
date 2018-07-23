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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
     */
    public function edit(ProductionStatus $status)
    {
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
     */
    public function update(TranslationRequest $request, ProductionStatus $status)
    {
        $data = $request->validated();

        foreach ($data as $localeCode => $translation) {
            $status->translations()->updateOrCreate(
                ['locale_code' => $localeCode],
                ['translation' => $translation]
            );
        }

        return redirect()->route('web.admin.starcitizen.production_statuses.index');
    }
}
