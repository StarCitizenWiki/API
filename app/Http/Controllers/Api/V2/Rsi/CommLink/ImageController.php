<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\Rsi\CommLink;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Rsi\CommLink\CommLinkResource;
use App\Http\Resources\Rsi\CommLink\Image\ImageResource;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImageController extends AbstractApiV2Controller
{
    #[OA\Get(
        path: '/api/v2/comm-link-images',
        tags: ['Comm-Links', 'RSI-Website', 'Images'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(name: 'filter[tags]', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Comm-Link Images',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/comm_link_image_v2')
                )
            )
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Image::class, $request)
            ->allowedFilters([
                AllowedFilter::partial('tags', 'tags.name'),
            ])
            ->orderByDesc('id')
            ->paginate($this->limit)
            ->appends(request()->query());

        return ImageResource::collection($query);
    }

    #[OA\Get(
        path: '/api/v2/comm-link-images/random',
        tags: ['Comm-Links', 'RSI-Website', 'Images'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(name: 'filter[tags]', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Retrieve a random Comm-Link Image. Limit parameter sets the number of random images',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/comm_link_image_v2')
                )
            )
        ]
    )]
    public function random(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Image::class, $request)
            ->allowedFilters([
                AllowedFilter::partial('tags', 'tags.name'),
            ])
            ->whereRelation('metadata', 'size', '>=', 250 * 1024)
            ->inRandomOrder()
            ->limit($request->has('limit') ? $this->limit : 1)
            ->get();

        return ImageResource::collection($query);
    }
}
