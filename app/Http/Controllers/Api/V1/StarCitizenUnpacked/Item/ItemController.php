<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizenUnpacked\Item;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\StarCitizenUnpacked\ItemSearchRequest;
use App\Models\StarCitizenUnpacked\Item;
use App\Services\Parser\StarCitizenUnpacked\Shops\Inventory;
use App\Transformers\Api\V1\StarCitizenUnpacked\ItemTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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

    /**
     * View all items
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->getResponse(Item::query()->orderBy('name'));
    }

    /**
     * View a singular item
     *
     * @param Request $request
     * @return Response
     * @throws ValidationException
     */
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
                ->where('name', $item)
                ->orWhere('uuid', $item)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $item));
        }

        return $this->getResponse($item);
    }

    /**
     * View a singular item
     *
     * @param ItemSearchRequest $request
     * @return Response
     */
    public function search(ItemSearchRequest $request): Response
    {
        $rules = (new ItemSearchRequest())->rules();
        $request->validate($rules);

        $query = urldecode($request->get('query'));

        try {
            $item = Item::query();

            if ($request->has('shop') && $request->get('shop') !== null) {
                $item
                    ->whereHas('shopsRaw', function ($query) use ($request) {
                        $query->where('shop_uuid', $request->get('shop'));
                    });
            }

            $item->where('name', 'like', "%{$query}%")
                ->orWhere('uuid', $query)
                ->orWhere('type', $query)
                ->orWhere('sub_type', $query);
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($item);
    }

    /**
     * View tradeables
     *
     * @return Response
     */
    public function indexTradeables(): Response
    {
        return $this->getResponse(
            Item::query()
                ->whereIn('type', Inventory::EXTRA_TYPES)
                ->orderBy('name')
        );
    }
}
