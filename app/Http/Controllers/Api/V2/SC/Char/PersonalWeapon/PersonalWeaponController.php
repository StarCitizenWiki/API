<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\SC\Char\PersonalWeapon;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Char\PersonalWeapon\PersonalWeaponResource;
use App\Http\Resources\SC\Item\ItemLinkResource;
use App\Http\Resources\SC\Item\ItemResource;
use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
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
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Personal Weapons',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item_link_v2')
                )
            )
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Item::class, $request)
            ->where('type', 'WeaponPersonal')
            ->paginate($this->limit)
            ->appends(request()->query());

        return ItemLinkResource::collection($query);
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
                    schema: 'personal_weapon_includes_v2',
                    collectionFormat: 'csv',
                    enum: [
                        'modes',
                        'damages',
                        'ports',
                        'shops',
                        'shops.items',
                    ]
                ),
                allowReserved: true
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
