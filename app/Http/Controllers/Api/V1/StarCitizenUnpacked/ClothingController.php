<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizenUnpacked;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\SC\Char\Clothing\Clothes;
use App\Models\StarCitizenUnpacked\CharArmor\CharArmor;
use App\Models\SC\Char\Clothing\Clothing;
use App\Transformers\Api\V1\StarCitizenUnpacked\ClothingTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class ClothingController extends ApiController
{
    /**
     * ClothingController constructor.
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
        return $this->getResponse(Clothes::query());
    }

    public function show(Request $request): Response
    {
        ['clothing' => $clothing] = Validator::validate(
            [
                'clothing' => $request->clothing,
            ],
            [
                'clothing' => 'required|string|min:1|max:255',
            ]
        );

        $clothing = $this->cleanQueryName($clothing);

        try {
            $clothing = Clothes::query()
                ->whereHas('item', function (Builder $query) use ($clothing) {
                    return $query->where('name', $clothing)->orWhere('uuid', $clothing);
                })
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $clothing));
        }

        return $this->getResponse($clothing);
    }
}
