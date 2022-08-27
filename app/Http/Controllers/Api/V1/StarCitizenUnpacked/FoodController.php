<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizenUnpacked;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\StarCitizenUnpacked\Food\Food;
use App\Transformers\Api\V1\StarCitizenUnpacked\Food\FoodTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class FoodController extends ApiController
{
    /**
     * ClothingController constructor.
     *
     * @param FoodTransformer $transformer
     * @param Request $request
     */
    public function __construct(FoodTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;

        parent::__construct($request);
    }

    public function index(): Response
    {
        return $this->getResponse(Food::query());
    }

    public function show(Request $request): Response
    {
        ['food' => $food] = Validator::validate(
            [
                'food' => $request->food,
            ],
            [
                'food' => 'required|string|min:1|max:255',
            ]
        );

        $food = $this->cleanQueryName($food);

        try {
            $food = Food::query()
                ->whereHas('item', function (Builder $query) use ($food) {
                    return $query->where('name', $food)->orWhere('uuid', $food);
                })
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $food));
        }

        return $this->getResponse($food);
    }
}
