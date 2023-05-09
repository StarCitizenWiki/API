<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\SC;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Item\ItemResource;
use App\Http\Resources\SC\Vehicle\VehicleWeaponResource;
use App\Models\SC\Item\Item;
use Dingo\Api\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VehicleWeaponController extends AbstractApiV2Controller
{

    #[OA\Get(
        path: '/api/v2/personal-weapons',
        tags: ['Stats', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of stats',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/stat_v2')
                )
            )
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Item::class)
            ->where('type', 'WeaponGun')
            ->limit($this->limit)
            ->allowedIncludes(VehicleWeaponResource::validIncludes())
            ->paginate()
            ->appends(request()->query());

        return ItemResource::collection($query);
    }

    public function show(Request $request): AbstractBaseResource
    {
        ['weapon' => $weapon] = Validator::validate(
            [
                'weapon' => $request->weapon,
            ],
            [
                'weapon' => 'required|string|min:1|max:255',
            ]
        );

        try {
            $weapon = QueryBuilder::for(Item::class, $request)
                ->where('type', 'WeaponGun')
                ->where(function(Builder $query) use ($weapon) {
                    $query->where('uuid', $weapon)
                        ->orWhere('name', 'LIKE', sprintf('%%%s%%', $weapon));
                })
                ->allowedIncludes(VehicleWeaponResource::validIncludes())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Weapon with specified UUID or Name found.');
        }

        return new ItemResource($weapon);
    }
}
