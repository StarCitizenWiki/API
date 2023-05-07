<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\SC\Char;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Char\ClothingResource;
use App\Http\Resources\SC\Char\PersonalWeaponResource;
use App\Http\Resources\StarCitizen\Stat\StatResource;
use App\Models\SC\Char\Clothing\Armor;
use App\Models\SC\Char\Clothing\Clothes;
use App\Models\SC\Char\PersonalWeapon\PersonalWeapon;
use App\Models\StarCitizen\Stat\Stat;
use App\Transformers\Api\V1\StarCitizen\Stat\StatTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArmorController extends AbstractApiV2Controller
{

    #[OA\Get(
        path: '/api/v2/personal-clothings',
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
        $query = QueryBuilder::for(Armor::class)
            ->limit($this->limit)
            ->allowedIncludes(ClothingResource::validIncludes())
            ->paginate()
            ->appends(request()->query());

        return ClothingResource::collection($query);
    }

    public function show($id, Request $request): AbstractBaseResource
    {
        ['clothing' => $clothing] = Validator::validate(
            [
                'clothing' => $request->clothing,
            ],
            [
                'clothing' => 'required|string|min:1|max:255',
            ]
        );

        try {
            $clothing = QueryBuilder::for(Armor::class)
                ->where('item_uuid', $clothing)
                ->orWhereRelation('item', 'name', 'LIKE', sprintf('%%%s%%', $clothing))
                ->allowedIncludes(ClothingResource::validIncludes())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Armor with specified UUID or Name found.');
        }

        return new ClothingResource($clothing);
    }
}
