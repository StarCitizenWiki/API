<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizenUnpacked;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\SC\Char\Clothing\Armor;
use App\Transformers\Api\V1\StarCitizenUnpacked\CharArmor\CharArmorTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\ClothingTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class CharArmorController extends ApiController
{
    /**
     * ShipController constructor.
     *
     * @param ClothingTransformer $transformer
     * @param Request $request
     */
    public function __construct(ClothingTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;

        parent::__construct($request);
    }

    public function index(): Response
    {
        return $this->getResponse(Armor::query());
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
            $armor = Armor::query()
                ->whereHas('item', function (Builder $query) use ($armor) {
                    return $query->where('name', $armor)->orWhere('uuid', $armor);
                })
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $armor));
        }

        return $this->getResponse($armor);
    }
}