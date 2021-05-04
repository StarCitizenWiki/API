<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizenUnpacked\WeaponPersonal;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonal;
use App\Transformers\Api\V1\StarCitizenUnpacked\WeaponPersonal\WeaponPersonalTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class WeaponPersonalController extends ApiController
{
    /**
     * ShipController constructor.
     *
     * @param WeaponPersonalTransformer $transformer
     * @param Request $request
     */
    public function __construct(WeaponPersonalTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;

        parent::__construct($request);
    }

    public function index(): Response
    {
        return $this->getResponse(WeaponPersonal::query()
            ->where('version', config(self::SC_DATA_KEY)));
    }

    public function show(Request $request): Response
    {
        ['weapon' => $weapon] = Validator::validate(
            [
                'weapon' => $request->weapon,
            ],
            [
                'weapon' => 'required|string|min:1|max:255',
            ]
        );

        $weapon = urldecode($weapon);

        try {
            $weapon = WeaponPersonal::query()
                ->where('version', config(self::SC_DATA_KEY))
                ->whereHas('item', function (Builder $query) use ($weapon) {
                    return $query->where('name', $weapon)
                        ->orWhere('uuid', $weapon);
                })
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $weapon));
        }

        return $this->getResponse($weapon);
    }
}
