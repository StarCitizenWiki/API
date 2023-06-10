<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizenUnpacked;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\StarCitizenUnpacked\Clothing;
use App\Transformers\Api\V1\StarCitizenUnpacked\ClothingTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        return $this->getResponse(Clothing::query());
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
            $clothing = Clothing::query()
                ->whereHas('item', function (Builder $query) use ($clothing) {
                    return $query->where('name', $clothing)->orWhere('uuid', $clothing);
                })
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $clothing)], 404);
        }

        return $this->getResponse($clothing);
    }
}
