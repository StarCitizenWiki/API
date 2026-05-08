<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'version_changelog',
    title: 'Version Changelog',
    properties: [
        new OA\Property(property: 'from_version', ref: '#/components/schemas/game_version'),
        new OA\Property(property: 'to_version', ref: '#/components/schemas/game_version'),
        new OA\Property(
            property: 'summary',
            description: 'Change counts keyed by entity type (item, vehicle, blueprint, mission, starmap_location)',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(
                properties: [
                    new OA\Property(property: 'added', type: 'integer', example: 47),
                    new OA\Property(property: 'removed', type: 'integer', example: 12),
                    new OA\Property(property: 'modified', type: 'integer', example: 234),
                ],
                type: 'object',
            ),
        ),
    ],
    type: 'object'
)]
class VersionChangelogResource extends AbstractBaseResource
{
    /**
     * @param  array<string, array{added: int, removed: int, modified: int}>  $summary
     */
    public function __construct($resource, private readonly array $summary)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'from_version' => new GameVersionResource($this->resource['from_version']),
            'to_version' => new GameVersionResource($this->resource['to_version']),
            'summary' => $this->summary,
        ];
    }
}
