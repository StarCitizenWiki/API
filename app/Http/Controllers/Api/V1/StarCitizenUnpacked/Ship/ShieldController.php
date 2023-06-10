<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizenUnpacked\Ship;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\StarCitizenUnpacked\ShipItem\Shield\Shield;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\ShipItemTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ShieldController extends ApiController
{
    /**
     * ShipController constructor.
     *
     * @param ShipItemTransformer $transformer
     * @param Request $request
     */
    public function __construct(ShipItemTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;

        parent::__construct($request);
    }

    public function index(): Response
    {
        return $this->getResponse(Shield::query());
    }

    public function show(Request $request): Response
    {
        ['item' => $item] = Validator::validate(
            [
                'item' => $request->item,
            ],
            [
                'item' => 'required|string|min:1|max:255',
            ]
        );

        $item = $this->cleanQueryName($item);

        try {
            $item = Shield::query()
                ->whereHas('shipItem.item', function (Builder $query) use ($item) {
                    return $query->where('name', $item)
                        ->orWhere('uuid', $item);
                })
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $item)], 404);
        }

        return $this->getResponse($item);
    }
}
