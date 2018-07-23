<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Type;

use App\Http\Controllers\Controller;
use App\Http\Requests\TranslationRequest;
use App\Models\Api\StarCitizen\Vehicle\Type\VehicleType;
use App\Models\System\Language;

/**
 * Class VehicleTypeController
 */
class VehicleTypeController extends Controller
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
            'admin.starcitizen.vehicles.types.index',
            [
                'translations' => VehicleType::all(),
                'languages' => Language::all(),
                'editRoute' => 'web.admin.starcitizen.vehicles.types.show',
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Api\StarCitizen\Vehicle\Type\VehicleType $type
     *
     * @return \Illuminate\Http\Response
     */
    public function show(VehicleType $type)
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.starcitizen.vehicles.types.show',
            [
                'translation' => $type,
                'updateRoute' => 'web.admin.starcitizen.vehicles.types.update',
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\TranslationRequest                $request
     * @param \App\Models\Api\StarCitizen\Vehicle\Type\VehicleType $type
     *
     * @return \Illuminate\Http\Response
     */
    public function update(TranslationRequest $request, VehicleType $type)
    {
        $data = $request->validated();

        foreach ($data as $localeCode => $translation) {
            $type->translations()->updateOrCreate(
                [
                    'locale_code' => $localeCode,
                    'translation' => $translation,
                ]
            );
        }

        return redirect()->route('web.admin.starcitizen.vehicles.types.index');
    }
}
