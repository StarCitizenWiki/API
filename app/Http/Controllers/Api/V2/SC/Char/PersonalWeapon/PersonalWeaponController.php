<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\SC\Char\PersonalWeapon;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Char\PersonalWeapon\PersonalWeaponLinkResource;
use App\Http\Resources\SC\Char\PersonalWeapon\PersonalWeaponResource;
use App\Http\Resources\SC\Item\ItemLinkResource;
use App\Http\Resources\SC\Item\ItemResource;
use App\Models\SC\Char\PersonalWeapon\PersonalWeapon;
use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PersonalWeaponController extends AbstractApiV2Controller
{
    #[OA\Get(
        path: '/api/v2/weapons',
        tags: ['In-Game', 'Item', 'Weapon'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(name: 'filter[type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[class]', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Personal Weapons',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/personal_weapon_link_v2')
                )
            )
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(PersonalWeapon::class, $request)
            ->allowedFilters([
                AllowedFilter::exact('type', 'weapon_type'),
                AllowedFilter::exact('class', 'weapon_class'),
            ])
            ->paginate($this->limit)
            ->appends(request()->query());

        return PersonalWeaponLinkResource::collection($query);
    }

    #[OA\Get(
        path: '/api/v2/weapons/{weapon}',
        tags: ['In-Game', 'Item', 'Weapon'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    description: 'Available Weapon includes',
                    type: 'array',
                    items: new OA\Items(
                        type: 'string',
                        enum: [
                            'ports',
                            'shops',
                            'shops.items',
                        ]
                    ),
                ),
                explode: false,
                allowReserved: true
            ),
            new OA\Parameter(
                name: 'weapon',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Weapon name of UUID',
                    type: 'string',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A Personal Weapon',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/personal_weapon_v2')
                )
            )
        ]
    )]
    public function show(Request $request): AbstractBaseResource
    {
        ['weapon' => $identifier] = Validator::validate(
            [
                'weapon' => $request->weapon,
            ],
            [
                'weapon' => 'required|string|min:1|max:255',
            ]
        );

        $identifier = $this->cleanQueryName($identifier);

        try {
            $identifier = QueryBuilder::for(Item::class, $request)
                ->where('type', 'WeaponPersonal')
                ->where(function (Builder $query) use ($identifier) {
                    $query->where('uuid', $identifier)
                        ->orWhere('name', $identifier);
                })
                ->orderByDesc('version')
                ->allowedIncludes(PersonalWeaponResource::validIncludes())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Weapon with specified UUID or Name found.');
        }

        return new ItemResource($identifier);
    }
}
