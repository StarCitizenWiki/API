<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\SC;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Requests\StarCitizenUnpacked\ItemSearchRequest;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Manufacturer\ManufacturerLinkResource;
use App\Http\Resources\SC\Manufacturer\ManufacturerResource;
use App\Models\SC\Manufacturer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ManufacturerController extends AbstractApiV2Controller
{
    #[OA\Get(
        path: '/api/v2/manufacturers',
        tags: ['In-Game', 'Manufacturer'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Manufacturers',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/manufacturer_link_v2')
                )
            )
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Manufacturer::class, $request)
            ->paginate($this->limit)
            ->appends(request()->query());

        return ManufacturerLinkResource::collection($query);
    }

    #[OA\Get(
        path: '/api/v2/manufacturers/{manufacturer}',
        tags: ['In-Game', 'Manufacturer'],
        parameters: [
            new OA\Parameter(
                name: 'manufacturer',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Manufacturer name, uuid, or code',
                    type: 'string',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A Manufacturer and its products',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/manufacturer_v2')
                )
            )
        ]
    )]
    public function show(Request $request): AbstractBaseResource
    {
        ['manufacturer' => $identifier] = Validator::validate(
            [
                'manufacturer' => $request->manufacturer,
            ],
            [
                'manufacturer' => 'required|string|min:1|max:255',
            ]
        );

        $identifier = $this->cleanQueryName($identifier);

        try {
            $shop = QueryBuilder::for(Manufacturer::class, $request)
                ->where('uuid', $identifier)
                ->orWhere('name', 'LIKE', sprintf('%%%s%%', $identifier))
                ->orWhere('code', 'LIKE', sprintf('%%%s%%', $identifier))
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Manufacturer with specified UUID or Name found.');
        }

        return new ManufacturerResource($shop);
    }

    #[OA\Post(
        path: '/api/v2/manufactureres/search',
        tags: ['In-Game', 'Manufacturer'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A List of matching Manufactureres',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/manufacturer_link_v2')
                )
            )
        ]
    )]
    public function search(ItemSearchRequest $request): JsonResource
    {
        $rules = (new ItemSearchRequest())->rules();
        $request->validate($rules);

        $query = $this->cleanQueryName($request->get('query'));

        $manufacturers = QueryBuilder::for(Manufacturer::class)
            ->where('name', 'like', "%{$query}%")
            ->orWhere('uuid', $query)
            ->orWhere('name', 'LIKE', sprintf('%%%s%%', $query))
            ->orWhere('code', 'LIKE', sprintf('%%%s%%', $query))
            ->groupBy('name')
            ->paginate($this->limit)
            ->appends(request()->query());

        if ($manufacturers->count() === 0) {
            throw new NotFoundHttpException(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return ManufacturerLinkResource::collection($manufacturers);
    }
}
