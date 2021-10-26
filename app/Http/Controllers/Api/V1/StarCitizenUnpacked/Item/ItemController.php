<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizenUnpacked\Item;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\StarCitizen\AbstractSearchRequest;
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
        return $this->getResponse(Item::query()->where('version', config(self::SC_DATA_KEY))->orderBy('name'));
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
                ->where('version', config(self::SC_DATA_KEY))
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
            $item = Item::query()
                ->where('version', config(self::SC_DATA_KEY))
                ->where('name', 'like', "%{$query}%")
                ->orWhere('uuid', $query)
                ->orWhere('type', $query)
                ->orWhere('sub_type', $query);
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($item);
    }

    /**
     * View only clothes
     *
     * @return Response
     */
    public function indexClothing(): Response
    {
        return $this->getResponse(
            Item::query()
                ->where('version', config(self::SC_DATA_KEY))
                ->where('type', 'LIKE', 'Char_Clothing%')
                ->orderBy('name')
        );
    }

    /**
     * View singular clothing item
     *
     * @param Request $request
     * @return Response
     * @throws ValidationException
     */
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
                ->where('version', config(self::SC_DATA_KEY))
                ->where('type', 'LIKE', 'Char_Clothing%')
                ->where('name', 'LIKE', sprintf('%%%s%%%%', $name))
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $name));
        }

        return $this->getResponse($name);
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
                ->where('version', config(self::SC_DATA_KEY))
                ->whereIn('type', Inventory::EXTRA_TYPES)
                ->orderBy('name')
        );
    }
}
