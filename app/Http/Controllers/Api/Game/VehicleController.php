<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Attributes\CacheTag;
use App\Http\Controllers\Api\Game\Concerns\FiltersJsonColumns;
use App\Http\Controllers\Controller;
use App\Http\Filters\SortByRelation;
use App\Http\Includes\CustomEagerLoadInclude;
use App\Http\Includes\IncludeDefinition;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Http\Resources\Game\Vehicle\VehicleResource;
use App\Http\Resources\StarCitizen\Vehicle\VehicleResource as ShipMatrixVehicleResource;
use App\Models\Game\ItemData;
use App\Models\Game\VehicleData;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle as ShipMatrixVehicle;
use App\Support\Filters\FilterCache;
use App\Support\Filters\FilterValues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CacheTag('vehicles')]
class VehicleController extends Controller
{
    use FiltersJsonColumns;
    use ResolvesGameVersion;

    protected function getJsonTableName(): string
    {
        return 'game_vehicle_data';
    }

    protected function getJsonColumnName(): string
    {
        return 'data';
    }

    #[OA\Get(
        path: '/api/ground-vehicles',
        operationId: 'listGroundVehicles',
        description: 'Alias for /api/vehicles scoped to ground vehicles (is_vehicle=true, is_gravlev=false, is_spaceship=false). Returns paginated in-game ground vehicles for the requested game version. Default includes: vehicle, gameVersion, manufacturer, shipMatrixVehicle.loaner, shipMatrixVehicle.skus.',
        summary: 'In-Game Ground Vehicles Overview',
        tags: ['Vehicles'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort field. Prefix with "-" for descending. Examples: name, -size, manufacturer.name, cargo_capacity, -speed.scm, shield.face_type. Use comma for multiple: size,-cargo_capacity',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    example: '-cargo_capacity'
                )
            ),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/vehicles/filters for valid values). Example: `Tumbril Land Systems`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer.name]', description: 'Alias for filter[manufacturer]. Example: `Anvil Aerospace`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[class_name]', description: 'Partial match on vehicle class name. Example: `TMBL_Nova`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on vehicle display name. Example: `Nova`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[query]', description: 'Search vehicles by name or class name. Example: `Nova`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', description: 'Exact match on vehicle size (1-6). Example: `3`', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[size_class]', description: 'Alias for filter[size]. Example: `3`', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[career]', description: 'Partial match on vehicle career. (see GET /api/vehicles/filters for valid values). Example: `Ground`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[role]', description: 'Partial match on vehicle role. (see GET /api/vehicles/filters for valid values). Example: `Combat`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[is_vehicle]', description: 'Filter to ground vehicles only.', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[is_gravlev]', description: 'Filter to gravlev vehicles only.', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[is_spaceship]', description: 'Filter to spaceships only.', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[mass_total]', description: 'Numeric filter on total mass (kg). Supports range operators. Example: `1521`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cargo_capacity]', description: 'Numeric filter on cargo capacity (SCU). Supports range operators. Example: `1`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[vehicle_inventory]', description: 'Numeric filter on vehicle inventory/stowage capacity. Supports range operators. Example: `0.13`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[crew.min]', description: 'Numeric filter on minimum crew count. Supports range operators. Example: `2`', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[health]', description: 'Numeric filter on vehicle health points. Supports range operators. Example: `4250`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[shield.hp]', description: 'Numeric filter on total shield hit points. Supports range operators. Example: `720`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[shield.face_type]', description: 'Shield face type. (see GET /api/vehicles/filters for valid values). Example: `Bubble`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[speed.scm]', description: 'Numeric filter on SCM speed. Supports range operators. Spaceship-only field; returns empty for ground vehicles. Example: `200`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[speed.max]', description: 'Numeric filter on maximum speed. Supports range operators. Spaceship-only field; returns empty for ground vehicles. Example: `1000`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[armor.health]', description: 'Numeric filter on armor health points. Supports range operators. Spaceship-only field; returns empty for ground vehicles. Example: `50000`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cross section.length]', description: 'Numeric filter on cross-section length (X axis). Supports range operators. Example: `100`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cross section.width]', description: 'Numeric filter on cross-section width (Y axis). Supports range operators. Example: `50`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cross section.height]', description: 'Numeric filter on cross-section height (Z axis). Supports range operators. Example: `30`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.ir_quantum]', description: 'Numeric filter on infrared quantum signature emission. Supports range operators. Spaceship-only field; returns empty for ground vehicles. Example: `100`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.ir_shields]', description: 'Numeric filter on infrared shield signature emission. Supports range operators. Spaceship-only field; returns empty for ground vehicles. Example: `100`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.em_quantum]', description: 'Numeric filter on electromagnetic quantum signature emission. Supports range operators. Spaceship-only field; returns empty for ground vehicles. Example: `100`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.em_shields]', description: 'Numeric filter on electromagnetic shield signature emission. Supports range operators. Spaceship-only field; returns empty for ground vehicles. Example: `100`', in: 'query', schema: new OA\Schema(type: 'number')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Ground Vehicles', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_vehicle')), new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'), new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta')], type: 'object')),
        ]
    )]
    #[OA\Get(
        path: '/api/gravlev-vehicles',
        operationId: 'listGravlevVehicles',
        description: 'Alias for /api/vehicles scoped to gravlev vehicles (is_gravlev=true). Returns paginated in-game gravlev vehicles for the requested game version. Default includes: vehicle, gameVersion, manufacturer, shipMatrixVehicle.loaner, shipMatrixVehicle.skus.',
        summary: 'In-Game Gravlev Vehicles Overview',
        tags: ['Vehicles'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort field. Prefix with "-" for descending. Examples: name, -size, manufacturer.name, cargo_capacity, -speed.scm, shield.face_type. Use comma for multiple: size,-cargo_capacity',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    example: '-cargo_capacity'
                )
            ),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/vehicles/filters for valid values). Example: `Drake Interplanetary`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer.name]', description: 'Alias for filter[manufacturer]. Example: `Origin Jumpworks`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[class_name]', description: 'Partial match on vehicle class name. Example: `Dragonfly`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on vehicle display name. Example: `Dragonfly`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[query]', description: 'Search vehicles by name or class name. Example: `Dragonfly`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', description: 'Exact match on vehicle size (1-6). Example: `1`', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[size_class]', description: 'Alias for filter[size]. Example: `1`', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[career]', description: 'Partial match on vehicle career. (see GET /api/vehicles/filters for valid values). Example: `Exploration`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[role]', description: 'Partial match on vehicle role. (see GET /api/vehicles/filters for valid values). Example: `Racing`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[is_vehicle]', description: 'Filter to ground vehicles only.', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[is_gravlev]', description: 'Filter to gravlev vehicles only.', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[is_spaceship]', description: 'Filter to spaceships only.', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[mass_total]', description: 'Numeric filter on total mass (kg). Supports range operators. Example: `2435`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cargo_capacity]', description: 'Numeric filter on cargo capacity (SCU). Supports range operators. Spaceship-only field; returns empty for gravlev vehicles. Example: `0`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[vehicle_inventory]', description: 'Numeric filter on vehicle inventory/stowage capacity. Supports range operators. Example: `0.39`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[crew.min]', description: 'Numeric filter on minimum crew count. Supports range operators. Example: `1`', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[health]', description: 'Numeric filter on vehicle health points. Supports range operators. Example: `1550`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[shield.hp]', description: 'Numeric filter on total shield hit points. Supports range operators. Example: `720`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[shield.face_type]', description: 'Shield face type. (see GET /api/vehicles/filters for valid values). Example: `Bubble`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[speed.scm]', description: 'Numeric filter on SCM speed. Supports range operators. Spaceship-only field; returns empty for gravlev vehicles. Example: `200`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[speed.max]', description: 'Numeric filter on maximum speed. Supports range operators. Spaceship-only field; returns empty for gravlev vehicles. Example: `1000`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[armor.health]', description: 'Numeric filter on armor health points. Supports range operators. Spaceship-only field; returns empty for gravlev vehicles. Example: `50000`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cross section.length]', description: 'Numeric filter on cross-section length (X axis). Supports range operators. Example: `0.39`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cross section.width]', description: 'Numeric filter on cross-section width (Y axis). Supports range operators. Example: `50`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cross section.height]', description: 'Numeric filter on cross-section height (Z axis). Supports range operators. Example: `30`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.ir_quantum]', description: 'Numeric filter on infrared quantum signature emission. Supports range operators. Spaceship-only field; returns empty for gravlev vehicles. Example: `100`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.ir_shields]', description: 'Numeric filter on infrared shield signature emission. Supports range operators. Spaceship-only field; returns empty for gravlev vehicles. Example: `100`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.em_quantum]', description: 'Numeric filter on electromagnetic quantum signature emission. Supports range operators. Spaceship-only field; returns empty for gravlev vehicles. Example: `100`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.em_shields]', description: 'Numeric filter on electromagnetic shield signature emission. Supports range operators. Spaceship-only field; returns empty for gravlev vehicles. Example: `100`', in: 'query', schema: new OA\Schema(type: 'number')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Gravlev Vehicles', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_vehicle')), new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'), new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta')], type: 'object')),
        ]
    )]
    #[OA\Get(
        path: '/api/vehicles',
        operationId: 'listVehicles',
        description: 'Returns paginated in-game vehicles for the requested game version. Default includes: vehicle, gameVersion, manufacturer, shipMatrixVehicle.loaner, shipMatrixVehicle.skus. Optional includes: shipMatrixVehicle, components, shipmatrixvehicle.components, hardpoints, ports.',
        summary: 'In-Game Vehicles Overview',
        tags: ['Vehicles'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort field. Prefix with "-" for descending. Examples: name, -size, manufacturer.name, cargo_capacity, -speed.scm, shield.face_type. Use comma for multiple: size,-cargo_capacity',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    example: '-cargo_capacity'
                )
            ),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/vehicles/filters for valid values). Example: `Aegis Dynamics`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer.name]', description: 'Alias for filter[manufacturer]. Example: `Anvil Aerospace`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[class_name]', description: 'Partial match on vehicle class name. Example: `TMBL_Nova`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on vehicle display name. Example: `Nova`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(
                name: 'filter[query]',
                description: 'Search vehicles by name or class name. Example: `Carrack`',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(name: 'filter[size]', description: 'Exact match on vehicle size (1-6). Example: `3`', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[size_class]', description: 'Alias for filter[size]. Example: `3`', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[career]', description: 'Partial match on vehicle career. (see GET /api/vehicles/filters for valid values). Example: `Exploration`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[role]', description: 'Partial match on vehicle role. (see GET /api/vehicles/filters for valid values). Example: `Racing`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[is_vehicle]', description: 'Filter to ground vehicles only.', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[is_gravlev]', description: 'Filter to gravlev vehicles only.', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[is_spaceship]', description: 'Filter to spaceships only.', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[mass_total]', description: 'Numeric filter on total mass (kg). Supports range operators. Example: `1521`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cargo_capacity]', description: 'Numeric filter on cargo capacity (SCU). Supports range operators. Example: `1`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[vehicle_inventory]', description: 'Numeric filter on vehicle inventory/stowage capacity. Supports range operators. Example: `0.13`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[crew.min]', description: 'Numeric filter on minimum crew count. Supports range operators. Example: `2`', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[health]', description: 'Numeric filter on vehicle health points. Supports range operators. Example: `1550`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[shield.hp]', description: 'Numeric filter on total shield hit points. Supports range operators. Example: `720`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[shield.face_type]', description: 'Shield face type. (see GET /api/vehicles/filters for valid values). Example: `Bubble`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[speed.scm]', description: 'Numeric filter on SCM speed. Supports range operators. Example: `220`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[speed.max]', description: 'Numeric filter on maximum speed. Supports range operators. Example: `1150`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[armor.health]', description: 'Numeric filter on armor health points. Supports range operators. Example: `10890`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cross section.length]', description: 'Numeric filter on cross-section length (X axis). Supports range operators. Example: `100`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cross section.width]', description: 'Numeric filter on cross-section width (Y axis). Supports range operators. Example: `50`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cross section.height]', description: 'Numeric filter on cross-section height (Z axis). Supports range operators. Example: `30`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.ir_quantum]', description: 'Numeric filter on infrared quantum signature emission. Supports range operators. Example: `10882`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.ir_shields]', description: 'Numeric filter on infrared shield signature emission. Supports range operators. Example: `10267`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.em_quantum]', description: 'Numeric filter on electromagnetic quantum signature emission. Supports range operators. Example: `53959`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.em_shields]', description: 'Numeric filter on electromagnetic shield signature emission. Supports range operators. Example: `17643`', in: 'query', schema: new OA\Schema(type: 'number')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Vehicles',
                content: new OA\JsonContent(
                    examples: [
                        new OA\Examples(
                            example: 'vehicle_search_page',
                            summary: 'Search vehicles by name',
                            value: [
                                'data' => [
                                    ['uuid' => '00000000-0000-0000-0000-000000000000', 'name' => 'Carrack', 'slug' => 'carrack'],
                                ],
                                'links' => ['first' => 'https://api.star-citizen.wiki/api/vehicles?page[number]=1', 'last' => null, 'prev' => null, 'next' => null],
                                'meta' => ['current_page' => 1, 'per_page' => 30, 'total' => 1],
                            ],
                        ),
                    ],
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_vehicle')),
                        new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta'),
                    ],
                    type: 'object',
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $this->buildBaseQuery($request);
        $vehicles = $query->jsonPaginate();
        $this->preloadIndexResourceDependencies($this->paginatedVehicleCollection($vehicles));

        return VehicleResource::collection($vehicles)
            ->additional(['meta' => ['valid_relations' => IncludeDefinition::toNames($this->includeDefinitions())]]);
    }

    #[OA\Get(
        path: '/api/ground-vehicles/{identifier}',
        operationId: 'getGroundVehicle',
        description: 'Alias for /api/vehicles/{identifier} scoped to ground vehicles. Results are scoped to the requested or default game version.',
        summary: 'In-Game Ground Vehicle Detail',
        tags: ['Vehicles'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Vehicle name, class_name, or UUID', type: 'string', example: 'tmbl-nova')),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'A Ground Vehicle', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', oneOf: [new OA\Schema(ref: '#/components/schemas/game_vehicle'), new OA\Schema(ref: '#/components/schemas/ship_matrix_vehicle')])], type: 'object')),
            new OA\Response(response: 404, description: 'Vehicle not found.', content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response')),
        ]
    )]
    #[OA\Get(
        path: '/api/gravlev-vehicles/{identifier}',
        operationId: 'getGravlevVehicle',
        description: 'Alias for /api/vehicles/{identifier} scoped to gravlev vehicles. Results are scoped to the requested or default game version.',
        summary: 'In-Game Gravlev Vehicle Detail',
        tags: ['Vehicles'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Vehicle name, class_name, or UUID', type: 'string', example: 'drak-dragonfly')),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'A Gravlev Vehicle', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', oneOf: [new OA\Schema(ref: '#/components/schemas/game_vehicle'), new OA\Schema(ref: '#/components/schemas/ship_matrix_vehicle')])], type: 'object')),
            new OA\Response(response: 404, description: 'Vehicle not found.', content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response')),
        ]
    )]
    #[OA\Get(
        path: '/api/vehicles/{identifier}',
        operationId: 'getVehicle',
        description: 'Retrieve a vehicle by name, class name, or UUID. Results are scoped to the requested or default game version. Loads manufacturer, gameVersion, shipMatrixVehicle (with foci, productionStatus, productionNote, type, size, loaner, skus, manufacturer, components), and port loadout items.',
        summary: 'In-Game Vehicle Detail',
        tags: ['Vehicles'],
        parameters: [
            new OA\Parameter(
                name: 'identifier',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Vehicle name, class_name, or UUID',
                    type: 'string',
                    example: 'Carrack',
                ),
            ),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A Vehicle',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', oneOf: [
                            new OA\Schema(ref: '#/components/schemas/game_vehicle'),
                            new OA\Schema(ref: '#/components/schemas/ship_matrix_vehicle'),
                        ]),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 404, description: 'Vehicle not found.', content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response')),
        ]
    )]
    public function show(Request $request, string $identifier): AbstractBaseResource
    {
        $original = urldecode($identifier);
        $versionCode = $this->gameVersionCode();
        $identifier = $this->cleanQueryName($identifier);
        $isUuid = Str::isUuid($identifier);
        $allowedIncludes = $this->allowedIncludes();
        $nameCandidates = array_values(array_unique([
            $identifier,
            str_replace('-', ' ', $identifier),
        ]));

        try {
            $vehicleData = QueryBuilder::for(VehicleData::class, $request)
                ->forRequestedOrDefaultVersion($versionCode)
                ->where(function (Builder $q) use ($nameCandidates, $isUuid, $identifier, $original) {
                    $hasConstraint = false;

                    if ($isUuid) {
                        $q->whereHas('vehicle', fn (Builder $itemQuery) => $itemQuery->where('uuid', $identifier));
                        $hasConstraint = true;
                    }

                    if (! $isUuid) {
                        $q->orWhereHas('vehicle', fn (Builder $itemQuery) => $itemQuery->where('slug', $identifier));
                        $hasConstraint = true;
                    }

                    foreach ($nameCandidates as $candidate) {
                        $underscored = str_replace(' ', '_', $candidate);
                        $boolean = $hasConstraint ? 'orWhere' : 'where';

                        $q->{$boolean}(function (Builder $nameQuery) use ($candidate, $underscored) {
                            $nameQuery->where('name', $candidate)
                                ->orWhereRaw('LOWER(name) = LOWER(?)', [$candidate])
                                ->orWhereRaw('LOWER(display_name) = LOWER(?)', [$candidate])
                                ->orWhere('class_name', strtoupper($underscored))
                                ->orWhereRaw('LOWER(class_name) = LOWER(?)', [$underscored]);
                        });

                        $hasConstraint = true;
                    }

                    $q->orWhereRaw('LOWER(class_name) = LOWER(?)', [$original]);
                })
                ->allowedIncludes(...$allowedIncludes)
                ->with(['vehicle', 'gameVersion', 'manufacturer'])
                ->first();

            if ($vehicleData === null || $vehicleData->vehicle === null) {
                $shipMatrixVehicle = ShipMatrixVehicle::query()
                    ->where(function (Builder $q) use ($nameCandidates, $original) {
                        foreach ($nameCandidates as $index => $candidate) {
                            $method = $index === 0 ? 'where' : 'orWhere';
                            $q->{$method}('name', $candidate);
                        }

                        $q->orWhere('slug', $original);
                    })
                    ->with([
                        'foci',
                        'manufacturer',
                        'productionStatus',
                        'productionNote',
                        'type',
                        'size',
                        'loaner',
                        ...$this->currentVersionShipMatrixScLoad('loaner.sc'),
                        'skus',
                    ])
                    ->first();

                if ($shipMatrixVehicle !== null) {
                    return new ShipMatrixVehicleResource($shipMatrixVehicle);
                }

                throw new ModelNotFoundException('No Vehicle with specified UUID or Name found.');
            }

            $shipMatrixRelations = [
                'shipMatrixVehicle.foci',
                'shipMatrixVehicle.productionStatus',
                'shipMatrixVehicle.productionNote',
                'shipMatrixVehicle.type',
                'shipMatrixVehicle.size',
                ...$this->currentVersionShipMatrixScLoad('shipMatrixVehicle.loaner.sc'),
                'shipMatrixVehicle.skus',
                'shipMatrixVehicle.manufacturer',
                'shipMatrixVehicle.components',
            ];

            $vehicleData->load($shipMatrixRelations);

            $vehicleData->load([
                'installedItems' => fn ($q) => $q->with(['item', 'manufacturer', 'gameVersion']),
            ]);
            $this->buildPortItemMap($vehicleData);
        } catch (ModelNotFoundException) {
            throw new NotFoundHttpException('No Vehicle with specified UUID or Name found.');
        }

        return (new VehicleResource($vehicleData))
            ->setValidIncludes(IncludeDefinition::toNames($this->includeDefinitions()));
    }

    #[OA\Post(
        path: '/api/ground-vehicles/search',
        operationId: 'searchGroundVehiclesDeprecated',
        description: 'Deprecated. Use GET /api/ground-vehicles?filter[name]={value} for name search. Scoped to ground vehicles. This endpoint will be removed in a future version.',
        summary: 'In-Game Ground Vehicle Search (Deprecated)',
        requestBody: new OA\RequestBody(description: 'Vehicle name, class_name, or UUID', required: true, content: [new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'object'), example: '{"query": "Nova"}')]),
        tags: ['Vehicles', 'Search'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of matching Ground Vehicles', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_vehicle')), new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'), new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta')], type: 'object')),
        ],
        deprecated: true
    )]
    #[OA\Post(
        path: '/api/gravlev-vehicles/search',
        operationId: 'searchGravlevVehiclesDeprecated',
        description: 'Deprecated. Use GET /api/gravlev-vehicles?filter[name]={value} for name search. Scoped to gravlev vehicles. This endpoint will be removed in a future version.',
        summary: 'In-Game Gravlev Vehicle Search (Deprecated)',
        requestBody: new OA\RequestBody(description: 'Vehicle name, class_name, or UUID', required: true, content: [new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'object'), example: '{"query": "Dragonfly"}')]),
        tags: ['Vehicles', 'Search'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of matching Gravlev Vehicles', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_vehicle')), new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'), new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta')], type: 'object')),
        ],
        deprecated: true
    )]
    #[OA\Post(
        path: '/api/vehicles/search',
        operationId: 'searchVehiclesDeprecated',
        description: 'Deprecated. Use GET /api/vehicles?filter[name]={value} for name search. This endpoint will be removed in a future version.',
        summary: 'In-Game Vehicle Search (Deprecated)',
        requestBody: new OA\RequestBody(
            description: 'Vehicle name, class_name, career, or UUID',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(type: 'object'),
                    example: '{"query": "Avenger"}',
                ),
            ]
        ),
        tags: ['Vehicles', 'Search'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer.name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[class_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[query]', description: 'Search vehicles by name or class name.', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[size_class]', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[career]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[role]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[is_vehicle]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[is_gravlev]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[is_spaceship]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[mass_total]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cargo_capacity]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[vehicle_inventory]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[crew.min]', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[health]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[shield.hp]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[shield.face_type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[speed.scm]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[speed.max]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[armor.health]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cross_section.length]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cross_section.width]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cross_section.height]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.ir_quantum]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.ir_shields]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.em_quantum]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.em_shields]', in: 'query', schema: new OA\Schema(type: 'number')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A List of matching Vehicles',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_vehicle')),
                        new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta'),
                    ],
                    type: 'object'
                )
            ),
        ],
        deprecated: true
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection|JsonResponse
    {
        $toSearch = $request->validated('query');
        $isUuid = Str::isUuid($toSearch);

        $query = $this->buildBaseQuery($request)
            ->where(function (Builder $query) use ($toSearch, $isUuid) {
                $pattern = "%{$toSearch}%";
                $underscoredPattern = '%'.str_replace(' ', '_', $toSearch).'%';

                $query->where('name', 'LIKE', $pattern)
                    ->orWhere('class_name', 'LIKE', $underscoredPattern)
                    ->orWhere('career', 'LIKE', $pattern);

                if ($isUuid) {
                    $query->orWhereHas('vehicle', fn (Builder $q) => $q->where('uuid', $toSearch));
                }
            });

        $vehicles = $query->jsonPaginate();
        $this->preloadIndexResourceDependencies($this->paginatedVehicleCollection($vehicles));

        return VehicleResource::collection($vehicles)
            ->additional([
                'meta' => ['deprecated' => true],
            ])->response()->header('Deprecated', 'true');
    }

    #[OA\Get(
        path: '/api/vehicles/filters',
        operationId: 'listVehicleFilters',
        description: 'Return all available filter values for in-game vehicles.',
        summary: 'In-Game Vehicle Filters',
        tags: ['Vehicles'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Available filters for in-game vehicles.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'filters',
                            properties: [
                                new OA\Property(property: 'manufacturer', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'is_vehicle', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'is_gravlev', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'is_spaceship', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'size', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'role', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'career', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'shield.face_type', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function filters(Request $request): JsonResponse
    {
        $versionCode = $this->gameVersionCode();
        $vehicleType = $request->route()->defaults['vehicle_type'] ?? 'vehicles';
        $resolver = function () use ($request, $versionCode, $vehicleType): array {
            $baseQuery = QueryBuilder::for(VehicleData::class, $request)
                ->forRequestedOrDefaultVersion($versionCode)
                ->forVehicleType($vehicleType)
                ->allowedFilters(...$this->allowedFilters());

            $facets = [
                'manufacturer' => [
                    'expr' => 'game_manufacturers.name',
                    'join' => static fn ($q) => $q->leftJoinRelationship('manufacturer'),
                    'cast' => null,
                ],
                'is_vehicle' => [
                    'expr' => 'game_vehicle_data.is_vehicle',
                    'cast' => static fn ($value) => $value === null ? null : (bool) $value,
                ],
                'is_gravlev' => [
                    'expr' => 'game_vehicle_data.is_gravlev',
                    'cast' => static fn ($value) => $value === null ? null : (bool) $value,
                ],
                'is_spaceship' => [
                    'expr' => 'game_vehicle_data.is_spaceship',
                    'cast' => static fn ($value) => $value === null ? null : (bool) $value,
                ],
                'size' => [
                    'expr' => 'game_vehicle_data.size',
                    'cast' => static fn ($value) => $value === null ? null : (int) $value,
                ],
                'role' => [
                    'expr' => 'game_vehicle_data.role',
                    'cast' => null,
                ],
                'career' => [
                    'expr' => 'game_vehicle_data.career',
                    'cast' => null,
                ],
                'shield.face_type' => [
                    'expr' => 'game_vehicle_data.shield_face_type',
                    'cast' => null,
                ],
            ];

            $out = [];

            foreach ($facets as $key => $facet) {
                $expr = $facet['expr'];

                $q = clone $baseQuery;

                if (isset($facet['join'])) {
                    ($facet['join'])($q);
                }

                $rows = $q
                    ->select([
                        DB::raw("{$expr} as value"),
                        DB::raw('count(*) as count'),
                    ])
                    ->groupByRaw($expr)
                    ->orderByRaw("{$expr} IS NULL, {$expr}")
                    ->get();

                $out[$key] = FilterValues::fromRows($rows, $facet['cast'] ?? null);
            }

            $out['max_medical_tier'] = FilterValues::fromRows(
                (clone $baseQuery)
                    ->selectRaw('game_vehicle_data.max_medical_tier as value, count(*) as count')
                    ->whereNotNull('game_vehicle_data.max_medical_tier')
                    ->groupByRaw('game_vehicle_data.max_medical_tier')
                    ->orderByRaw('game_vehicle_data.max_medical_tier')
                    ->get(),
            );

            return $out;
        };

        if (FilterCache::hasEffectiveFilters($request->input('filter', []))) {
            $filters = $resolver();
        } else {
            $filters = FilterCache::rememberForever(
                FilterCache::NAMESPACE_VEHICLES,
                FilterCache::vehiclesKey($versionCode, $vehicleType),
                $resolver
            );
        }

        return response()->json([
            'filters' => $filters,
        ]);
    }

    /**
     * Build base query with filters, sorts, and includes for vehicles.
     */
    private function buildBaseQuery(Request $request): QueryBuilder
    {
        $versionCode = $this->gameVersionCode();
        $vehicleType = $request->route()->defaults['vehicle_type'] ?? 'vehicles';

        return QueryBuilder::for(VehicleData::class, $request)
            ->forRequestedOrDefaultVersion($versionCode)
            ->forVehicleType($vehicleType)
            ->when(
                ! $request->filled('filter.include_irrelevant'),
                fn ($q) => $q->where('game_vehicle_data.is_player_relevant', true),
            )
            ->allowedFilters(...$this->allowedFilters())
            ->allowedSorts(...$this->allowedSorts())
            ->defaultSort('name')
            ->allowedIncludes(...$this->allowedIncludes())
            ->with([
                'vehicle',
                'gameVersion',
                'manufacturer',
                'shipMatrixVehicle.foci',
                'shipMatrixVehicle.productionStatus',
                'shipMatrixVehicle.productionNote',
                'shipMatrixVehicle.type',
                'shipMatrixVehicle.size',
                ...$this->currentVersionShipMatrixScLoad('shipMatrixVehicle.loaner.sc'),
                'shipMatrixVehicle.skus',
            ]);
    }

    /**
     * @return array<string, callable>
     */
    private function currentVersionShipMatrixScLoad(string $relation): array
    {
        $versionId = $this->gameVersion()->id;

        return [
            $relation => static function ($query) use ($versionId): void {
                $query
                    ->where('game_vehicle_data.game_version_id', $versionId)
                    ->with('vehicle');
            },
        ];
    }

    /**
     * @return Collection<int, VehicleData>
     */
    private function paginatedVehicleCollection(mixed $vehicles): Collection
    {
        if (method_exists($vehicles, 'getCollection')) {
            return $vehicles->getCollection();
        }

        if (method_exists($vehicles, 'items')) {
            return collect($vehicles->items());
        }

        return collect();
    }

    /**
     * Preload data used during VehicleResource collection rendering.
     *
     * Resource rendering must not call the database per vehicle. The index/search
     * payload computes armor from the vehicle JSON and expands UEX prices, so load
     * those dependencies once for the current page and store them on the request.
     *
     * @param  Collection<int, VehicleData>  $vehicles
     */
    private function preloadIndexResourceDependencies(Collection $vehicles): void
    {
        if ($vehicles->isEmpty()) {
            return;
        }

        $this->preloadArmorItems($vehicles);
        $this->preloadUexLocationData($vehicles);
    }

    /**
     * @param  Collection<int, VehicleData>  $vehicles
     */
    private function preloadArmorItems(Collection $vehicles): void
    {
        $armorUuids = $vehicles
            ->map(fn (VehicleData $vehicleData): mixed => Arr::get($vehicleData->data ?? [], 'Armor.UUID'))
            ->filter()
            ->unique()
            ->values();

        if ($armorUuids->isEmpty()) {
            return;
        }

        $version = $this->gameVersion();

        $itemData = ItemData::query()
            ->where('game_version_id', $version->id)
            ->whereHas('item', static fn (Builder $query): Builder => $query->whereIn('uuid', $armorUuids->all()))
            ->with(['item', 'manufacturer', 'gameVersion', 'variantGroupItem'])
            ->get()
            ->keyBy(fn (ItemData $itemData): string => $itemData->item->uuid);

        request()->attributes->set('eager_loaded_game_items', $itemData);
    }

    /**
     * @param  Collection<int, VehicleData>  $vehicles
     */
    private function preloadUexLocationData(Collection $vehicles): void
    {
        $locationDataIds = $vehicles
            ->flatMap(static function (VehicleData $vehicleData): array {
                return collect($vehicleData->uex_purchase_prices ?? [])
                    ->merge($vehicleData->uex_rental_prices ?? [])
                    ->pluck('starmap_location_data_id')
                    ->all();
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        VehicleResource::preloadLocationData($locationDataIds);
    }

    /**
     * @return array<int, IncludeDefinition>
     */
    private function includeDefinitions(): array
    {
        return [
            IncludeDefinition::relationship('shipMatrixVehicle'),
            IncludeDefinition::relationship('components', 'shipMatrixVehicle.components'),
            IncludeDefinition::relationship('shipmatrixvehicle.components', 'shipMatrixVehicle.components'),
            IncludeDefinition::custom('hardpoints', new CustomEagerLoadInclude),
            IncludeDefinition::custom('ports', new CustomEagerLoadInclude),
        ];
    }

    /**
     * Query builder includes for vehicles.
     */
    private function allowedIncludes(): array
    {
        return IncludeDefinition::toSpatieIncludes($this->includeDefinitions());
    }

    /**
     * Allowed filters for in-game vehicles, including JSON-backed fields.
     */
    private function allowedFilters(): array
    {
        $manufacturerFilter = static function (Builder $query, mixed $value): void {
            $values = is_array($value) ? $value : [$value];

            $query->whereHas('manufacturer', static function ($manufacturerQuery) use ($values): void {
                $manufacturerQuery
                    ->whereIn('name', $values)
                    ->orWhereIn('code', $values);
            });
        };

        return [
            AllowedFilter::callback('manufacturer', $manufacturerFilter),
            AllowedFilter::callback('manufacturer.name', $manufacturerFilter),
            AllowedFilter::callback('class_name', static function (Builder $query, mixed $value): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $like = DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
                $query->where('game_vehicle_data.class_name', $like, "%{$value}%");
            }),
            AllowedFilter::callback('name', static function (Builder $query, mixed $value): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $like = DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
                $query->where('game_vehicle_data.name', $like, "%{$value}%");
            }),
            AllowedFilter::exact('career'),
            AllowedFilter::exact('role'),
            AllowedFilter::exact('is_vehicle'),
            AllowedFilter::exact('is_gravlev'),
            AllowedFilter::exact('is_spaceship'),
            AllowedFilter::callback('include_irrelevant', static function (Builder $query): void {
                // noop
            }),
            AllowedFilter::exact('size'),
            AllowedFilter::exact('size_class', 'size'),
            AllowedFilter::callback('mass_total', function (Builder $query, mixed $value): void {
                $this->applyColumnFilter($query, 'mass_total', $value);
            }),
            AllowedFilter::callback('cargo_capacity', function (Builder $query, mixed $value): void {
                $this->applyColumnFilter($query, 'cargo_capacity', $value);
            }),
            AllowedFilter::callback('vehicle_inventory', function (Builder $query, mixed $value): void {
                $this->applyColumnFilter($query, 'vehicle_inventory', $value);
            }),
            AllowedFilter::callback('crew.min', function (Builder $query, mixed $value): void {
                $this->applyColumnFilter($query, 'crew_min', $value);
            }),
            AllowedFilter::callback('crew.max', function (Builder $query, mixed $value): void {
                $this->applyColumnFilter($query, 'crew_max', $value);
            }),
            AllowedFilter::callback('health', function (Builder $query, mixed $value): void {
                $this->applyColumnFilter($query, 'health', $value);
            }),
            AllowedFilter::callback('shield.hp', function (Builder $query, mixed $value): void {
                $this->applyColumnFilter($query, 'shield_hp', $value);
            }),
            AllowedFilter::callback('shield.face_type', function (Builder $query, mixed $value): void {
                $this->applyColumnFilter($query, 'shield_face_type', $value);
            }),
            AllowedFilter::callback('speed.scm', function (Builder $query, mixed $value): void {
                $this->applyColumnFilter($query, 'speed_scm', $value);
            }),
            AllowedFilter::callback('speed.max', function (Builder $query, mixed $value): void {
                $this->applyColumnFilter($query, 'speed_max', $value);
            }),
            AllowedFilter::callback('armor.health', function (Builder $query, mixed $value): void {
                $this->applyColumnFilter($query, 'armor_health', $value);
            }),
            AllowedFilter::callback('cross_section.length', function (Builder $query, mixed $value): void {
                $this->applyColumnFilter($query, 'cross_section_length', $value);
            }),
            AllowedFilter::callback('cross_section.width', function (Builder $query, mixed $value): void {
                $this->applyColumnFilter($query, 'cross_section_width', $value);
            }),
            AllowedFilter::callback('cross_section.height', function (Builder $query, mixed $value): void {
                $this->applyColumnFilter($query, 'cross_section_height', $value);
            }),
            AllowedFilter::callback('signature.ir_quantum', function (Builder $query, mixed $value): void {
                $this->applyColumnFilter($query, 'signature_ir_quantum', $value);
            }),
            AllowedFilter::callback('signature.ir_shields', function (Builder $query, mixed $value): void {
                $this->applyColumnFilter($query, 'signature_ir_shields', $value);
            }),
            AllowedFilter::callback('signature.em_quantum', function (Builder $query, mixed $value): void {
                $this->applyColumnFilter($query, 'signature_em_quantum', $value);
            }),
            AllowedFilter::callback('signature.em_shields', function (Builder $query, mixed $value): void {
                $this->applyColumnFilter($query, 'signature_em_shields', $value);
            }),
            AllowedFilter::callback('has_medical_beds', static function (Builder $query, mixed $value): void {
                $hasMedicalBeds = filter_var($value, FILTER_VALIDATE_BOOLEAN);

                if ($hasMedicalBeds) {
                    $query->whereNotNull('max_medical_tier');
                } else {
                    $query->whereNull('max_medical_tier');
                }
            }),
            AllowedFilter::callback('max_medical_tier', function (Builder $query, mixed $value): void {
                $this->applyColumnFilter($query, 'max_medical_tier', $value);
            }),
            AllowedFilter::callback('query', static function (Builder $query, mixed $value): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $like = DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
                $pattern = "%{$value}%";

                $query->where(static function (Builder $q) use ($pattern, $like): void {
                    $q->whereRaw("game_vehicle_data.name {$like} ?", [$pattern])
                        ->orWhereRaw("game_vehicle_data.class_name {$like} ?", [$pattern]);
                });
            }),
        ];
    }

    /**
     * Allowed sorts for in-game vehicles, including JSON-backed fields.
     */
    private function allowedSorts(): array
    {
        return array_merge(
            [
                AllowedSort::callback('name', static function (Builder $query, bool $descending): void {
                    $direction = $descending ? 'desc' : 'asc';

                    $query->orderByRaw(
                        "coalesce(game_vehicle_data.display_name, game_vehicle_data.name) {$direction}"
                    );
                }),
                'class_name',
                'career',
                'role',
                'is_vehicle',
                'is_gravlev',
                'is_spaceship',
                'size',
                AllowedSort::custom('manufacturer', new SortByRelation, 'manufacturer.name'),
                AllowedSort::custom('manufacturer.name', new SortByRelation, 'manufacturer.name'),
                AllowedSort::custom('msrp', new SortByRelation, 'shipmatrixVehicle.msrp'),
                AllowedSort::field('size_class', 'size'),

                AllowedSort::field('length', 'length'),
                AllowedSort::field('width', 'width'),
                AllowedSort::field('height', 'height'),
                AllowedSort::field('mass_total', 'mass_total'),
                AllowedSort::callback('cargo_capacity', static function (Builder $query, bool $descending): void {
                    $direction = $descending ? 'desc' : 'asc';
                    $query->orderByRaw("cargo_capacity {$direction} NULLS LAST");
                }),
                AllowedSort::field('vehicle_inventory', 'vehicle_inventory'),
                AllowedSort::field('crew.min', 'crew_min'),
                AllowedSort::field('crew.max', 'crew_max'),
                AllowedSort::callback('health', static function (Builder $query, bool $descending): void {
                    $direction = $descending ? 'desc' : 'asc';
                    $query->orderByRaw("health {$direction} NULLS LAST");
                }),
                AllowedSort::field('armor.health', 'armor_health'),
                AllowedSort::field('shield.hp', 'shield_hp'),
                AllowedSort::field('shield.face_type', 'shield_face_type'),
                AllowedSort::field('speed.scm', 'speed_scm'),
                AllowedSort::field('speed.max', 'speed_max'),
                AllowedSort::field('cross_section.length', 'cross_section_length'),
                AllowedSort::field('cross_section.width', 'cross_section_width'),
                AllowedSort::field('cross_section.height', 'cross_section_height'),
                AllowedSort::field('signature.ir_quantum', 'signature_ir_quantum'),
                AllowedSort::field('signature.ir_shields', 'signature_ir_shields'),
                AllowedSort::field('signature.em_quantum', 'signature_em_quantum'),
                AllowedSort::field('signature.em_shields', 'signature_em_shields'),
            ],
            $this->allowedJsonSorts()
        );
    }

    /**
     * Get JSON-backed sort fields from configuration.
     *
     * @return array<AllowedSort>
     */
    private function allowedJsonSorts(): array
    {
        $sortConfig = config('sorts.vehicles', []);
        $allowedSorts = [];

        foreach ($sortConfig as $config) {
            $allowedSorts[] = $this->jsonSort(
                // Filter only by path in raw json
                $config['path'],
                $config['path'],
                $config['cast'] ?? 'numeric'
            );
        }

        return $allowedSorts;
    }

    /**
     * Build UUID->Item lookup from loaded installedItems pivot for PortResource.
     */
    private function buildPortItemMap(VehicleData $vehicleData): void
    {
        if (! $vehicleData->relationLoaded('installedItems')) {
            return;
        }

        $items = $vehicleData->installedItems->mapWithKeys(function (ItemData $itemData) {
            $item = $itemData->item;
            if ($item === null) {
                return [];
            }
            $item->setRelation('data', collect([$itemData]));

            return [$item->uuid => $item];
        });

        request()->attributes->set('eager_loaded_port_items', $items);
    }

    /**
     * Build merged UUID->Item lookup for a collection of VehicleData.
     */
    private function buildBatchPortItemMap(Collection $vehicleDataCollection): void
    {
        $items = collect();

        foreach ($vehicleDataCollection as $vehicleData) {
            if (! $vehicleData->relationLoaded('installedItems')) {
                continue;
            }

            foreach ($vehicleData->installedItems as $itemData) {
                $item = $itemData->item;
                if ($item === null || $items->has($item->uuid)) {
                    continue;
                }
                $item->setRelation('data', collect([$itemData]));
                $items[$item->uuid] = $item;
            }
        }

        request()->attributes->set('eager_loaded_port_items', $items);
    }
}
