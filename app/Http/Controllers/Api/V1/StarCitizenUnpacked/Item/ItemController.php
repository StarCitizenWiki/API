<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizenUnpacked\Item;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\StarCitizenUnpacked\Item;
use App\Services\Parser\StarCitizenUnpacked\Shops\Inventory;
use App\Transformers\Api\V1\StarCitizenUnpacked\ItemTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class ItemController extends ApiController
{
    /**
     * ShipController constructor.
     *
     * @param ItemTransformer $transformer
     * @param Request $request
     */
    public function __construct(ItemTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;

        parent::__construct($request);
    }

    public function index(): Response
    {
        return $this->getResponse(Item::query()->orderBy('name'));
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

        $item = urldecode($item);

        try {
            $item = Item::query()
                ->where('name', 'LIKE', sprintf('%%%s%%%%', $item))
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $item));
        }

        return $this->getResponse($item);
    }

    public function indexClothing(): Response
    {
        return $this->getResponse(Item::query()->where('type', 'LIKE', 'Char_Clothing%')->orderBy('name'));
    }

    public function showClothing(Request $request): Response
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

        try {
            $name = Item::query()
                ->where('type', 'LIKE', 'Char_Clothing%')
                ->where('name', 'LIKE', sprintf('%%%s%%%%', $name))
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $name));
        }

        return $this->getResponse($name);
    }

    public function indexTradeables(): Response
    {
        return $this->getResponse(Item::query()->whereIn('type', Inventory::UNKNOWN_TYPES)->orderBy('name'));
    }
}
