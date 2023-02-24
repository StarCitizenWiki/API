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
    /** @var array Fixes item names to uuids with shops */
    private array $uuidFixes = [
        'Helix I Mining Laser' => '81e1a10a-c7bd-401f-92e1-284115dcd6e1',
        '153d53e7-c5e0-445c-82ac-6aae2073b565' => '81e1a10a-c7bd-401f-92e1-284115dcd6e1',

        'Impact I Mining Laser' => '6429e3d3-c813-4dfc-bc68-c95b54123722',
        'af6cede9-7ae7-47a6-ba91-dac5f020f698' => '6429e3d3-c813-4dfc-bc68-c95b54123722',

        'Hofstede-S1 Mining Laser' => '077a4a94-6296-4a83-a6c4-f215f7efd1df',
        'a5b839fe-c1cc-4cbd-abb6-c9296ad84d46' => '077a4a94-6296-4a83-a6c4-f215f7efd1df',

        'Klein-S1 Mining Laser' => 'e6b284b9-456a-4444-b5fc-7c33bf5a6945',
        'd04aed0b-3c4a-4aaf-96c6-1abf8b32c12a' => 'e6b284b9-456a-4444-b5fc-7c33bf5a6945',
    ];

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
        return $this->getResponse(ShipItem::query()->orderBy('name'));
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

        $item = $this->cleanQueryName($item);

        if (isset($this->uuidFixes[$item])) {
            $item = $this->uuidFixes[$item];
        }

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

        $query = $this->cleanQueryName($request->get('query'));

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
