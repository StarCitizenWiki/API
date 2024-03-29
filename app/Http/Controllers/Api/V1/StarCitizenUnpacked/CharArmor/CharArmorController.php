<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizenUnpacked\CharArmor;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\StarCitizenUnpacked\CharArmor\CharArmor;
use App\Transformers\Api\V1\StarCitizenUnpacked\CharArmor\CharArmorTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class CharArmorController extends ApiController
{
    /**
     * ShipController constructor.
     *
     * @param CharArmorTransformer $transformer
     * @param Request $request
     */
    public function __construct(CharArmorTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;

        parent::__construct($request);
    }

    public function index(): Response
    {
        return $this->getResponse(CharArmor::query());
    }

    public function show(Request $request): Response
    {
        ['armor' => $armor] = Validator::validate(
            [
                'armor' => $request->armor,
            ],
            [
                'armor' => 'required|string|min:1|max:255',
            ]
        );

        $armor = $this->cleanQueryName($armor);

        try {
            $armor = CharArmor::query()
                ->whereHas('item', function (Builder $query) use ($armor) {
                    return $query->where('name', $armor)->orWhere('uuid', $armor);
                })
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $armor)], 404);
        }

        return $this->getResponse($armor);
    }
}
