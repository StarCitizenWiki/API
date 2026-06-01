<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Support\Starmap\SystemOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use JsonException;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/locations/positions',
    operationId: 'listLocationPositions',
    description: 'Starmap entity world positions.',
    summary: 'Starmap Entity Positions',
    tags: ['Starmap'],
    parameters: [
        new OA\Parameter(
            name: 'filter[type]',
            description: 'Exact match on entity type (Planet, Moon, Star, Manmade, Asteroid, Anomaly, JumpPoint, etc.)',
            in: 'query',
            schema: new OA\Schema(type: 'string', example: 'Planet')
        ),
        new OA\Parameter(
            name: 'filter[system]',
            description: 'Exact match on system name (stanton, pyro, nyx)',
            in: 'query',
            schema: new OA\Schema(type: 'string', example: 'stanton')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Entity positions and jump point connections',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'name', type: 'string'),
                                new OA\Property(property: 'type', type: 'string'),
                                new OA\Property(property: 'system', type: 'string'),
                                new OA\Property(property: 'parent_uuid', type: 'string', format: 'uuid', nullable: true),
                                new OA\Property(property: 'x', type: 'number', format: 'float'),
                                new OA\Property(property: 'y', type: 'number', format: 'float'),
                                new OA\Property(property: 'z', type: 'number', format: 'float'),
                            ],
                            type: 'object'
                        )
                    ),
                    new OA\Property(
                        property: 'connections',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'entry_uuid', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'exit_uuid', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'entry_system', type: 'string'),
                                new OA\Property(property: 'exit_system', type: 'string'),
                                new OA\Property(property: 'fuel_cost', type: 'integer'),
                                new OA\Property(property: 'size_class', type: 'string', enum: ['small', 'large', 'unknown']),
                            ],
                            type: 'object'
                        )
                    ),
                ],
                type: 'object'
            )
        ),
    ]
)]
class StarmapPositionController
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $this->loadData();

        $entities = collect($data['entities'] ?? []);

        $type = $request->input('filter.type');
        $system = $request->input('filter.system');

        if ($type !== null && $type !== '') {
            $entities = $entities->where('type', $type);
        }

        if ($system !== null && $system !== '') {
            $entities = $entities->where('system', $system);
        }

        $systemOrder = SystemOrder::SYSTEMS;

        $sorted = $entities->sortBy(function (array $entity) use ($systemOrder): array {
            $sysIndex = array_search($entity['system'], $systemOrder, true);

            return [($sysIndex === false ? 999 : $sysIndex), $entity['name'] ?? ''];
        });

        return response()->json([
            'data' => $sorted->values()->all(),
            'connections' => $data['connections'] ?? [],
        ]);
    }

    /**
     * @return array{entities: list<array<string, mixed>>, connections: list<array<string, mixed>>}
     *
     * @throws JsonException
     */
    private function loadData(): array
    {
        $contents = Storage::disk('scunpacked')->get('starmap_positions.json');

        if ($contents === null) {
            abort(503, 'Starmap position data is currently unavailable.');
        }

        return json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    }
}
