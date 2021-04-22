<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizenUnpacked\Shop;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\StarCitizenUnpacked\Shop\Shop;
use App\Transformers\Api\V1\StarCitizenUnpacked\Shop\ShopTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class ShopController extends ApiController
{
    /**
     * ShipController constructor.
     *
     * @param ShopTransformer $transformer
     * @param Request $request
     */
    public function __construct(ShopTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;

        parent::__construct($request);
    }

    public function index(): Response
    {
        return $this->getResponse(Shop::query()->orderBy('name'));
    }

    public function show(Request $request): Response
    {
        ['shop' => $shop] = Validator::validate(
            [
                'shop' => $request->shop,
            ],
            [
                'shop' => 'required|string|min:1|max:255',
            ]
        );

        $shop = urldecode($shop);

        try {
            $shop = Shop::query()
                ->where('name_raw', 'LIKE', sprintf('%%%s%%%%', $shop))
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $shop));
        }

        return $this->getResponse($shop);
    }
}
