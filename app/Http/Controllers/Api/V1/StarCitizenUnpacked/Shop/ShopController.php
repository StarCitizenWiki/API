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
        return $this->getResponse(Shop::query()
            ->where('version', config(self::SC_DATA_KEY)));
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
                ->where('version', config(self::SC_DATA_KEY))
                ->where('name_raw', 'LIKE', sprintf('%%%s%%%%', $shop))
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $shop));
        }

        return $this->getResponse($shop);
    }

    public function showPosition(Request $request): Response
    {
        ['position' => $position] = Validator::validate(
            [
                'position' => $request->position,
            ],
            [
                'position' => 'required|string|min:1|max:255',
            ]
        );

        $position = urldecode($position);
        $positions = Shop::query()
            ->where('version', config(self::SC_DATA_KEY))
            ->where('position', 'LIKE', sprintf('%%%s%%%%', $position))
            ->get();

        if ($positions->isEmpty()) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $position));
        }

        return $this->getResponse($positions);
    }

    public function showName(Request $request): Response
    {
        ['name' => $name] = Validator::validate(
            [
                'name' => $request->name,
            ],
            [
                'name' => 'required|string|min:1|max:255',
            ]
        );

        $name = urldecode($name);
        $positions = Shop::query()
            ->where('version', config(self::SC_DATA_KEY))
            ->where('name', 'LIKE', sprintf('%%%s%%%%', $name))
            ->get();

        if ($positions->isEmpty()) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $name));
        }

        return $this->getResponse($positions);
    }

    public function showShopAtPosition(Request $request): Response
    {
        ['position' => $position, 'name' => $name] = Validator::validate(
            [
                'position' => $request->position,
                'name' => $request->name,
            ],
            [
                'position' => 'required|string|min:1|max:255',
                'name' => 'required|string|min:1|max:255',
            ]
        );

        $position = urldecode($position);
        $name = urldecode($name);

        try {
            $shop = Shop::query()
                ->where('version', config(self::SC_DATA_KEY))
                ->where('position', $position)
                ->where('name', $name)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $position));
        }

        return $this->getResponse($shop);
    }
}
