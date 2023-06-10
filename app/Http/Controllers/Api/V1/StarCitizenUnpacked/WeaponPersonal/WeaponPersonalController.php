<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizenUnpacked\WeaponPersonal;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonal;
use App\Transformers\Api\V1\StarCitizenUnpacked\WeaponPersonal\WeaponPersonalTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

        $weapon = $this->cleanQueryName($weapon);

        try {
            $weapon = WeaponPersonal::query()
                ->whereHas('item', function (Builder $query) use ($weapon) {
                    return $query->where('name', $weapon)
                        ->orWhere('uuid', $weapon);
                })
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $weapon)], 404);
        }

        return $this->getResponse($weapon);
    }
}
