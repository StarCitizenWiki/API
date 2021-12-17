<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizenUnpacked\Ship;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\StarCitizenUnpacked\ItemSearchRequest;
use App\Models\StarCitizenUnpacked\ShipItem\ShipItem;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\ShipItemTransformer;
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
     * @param ShipItemTransformer $transformer
     * @param Request $request
     */
    public function __construct(ShipItemTransformer $transformer, Request $request)
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
        return $this->getResponse(ShipItem::query()->where('version', config(self::SC_DATA_KEY))->orderBy('name'));
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
            $item = ShipItem::query()
                ->whereRelation('item', 'name', $item)
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
            $item = ShipItem::query()
                ->whereRelation('item', 'name', 'like', "%{$query}%")
                ->orWhere('uuid', $query);
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($item);
    }
}
