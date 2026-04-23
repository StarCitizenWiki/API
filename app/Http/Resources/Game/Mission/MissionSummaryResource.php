<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Mission;

use App\Http\Resources\AbstractBaseResource;
use App\Support\Formatting\FormatMissionTitle;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'mission_summary',
    title: 'Mission Summary',
    description: 'Lightweight mission summary used in location and other nested responses.',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'title', type: 'string', nullable: true),
        new OA\Property(property: 'mission_type', type: 'string', nullable: true),
        new OA\Property(property: 'illegal', type: 'boolean'),
        new OA\Property(property: 'has_combat', type: 'boolean', nullable: true),
        new OA\Property(
            property: 'faction',
            properties: [
                new OA\Property(property: 'name', type: 'string', nullable: true),
                new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
                new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(property: 'link', type: 'string', format: 'uri'),
        new OA\Property(property: 'web_url', type: 'string', format: 'uri'),
    ],
    type: 'object'
)]
class MissionSummaryResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        $mission = $this->resource->mission;

        return [
            'uuid' => $mission?->uuid,
            'title' => FormatMissionTitle::format($this->resource->title, $this->resource->debug_name),
            'mission_type' => $this->resource->mission_type,
            'illegal' => $this->resource->illegal,
            'has_combat' => $this->resource->has_combat,
            'faction' => $this->resource->faction ? [
                'name' => $this->resource->faction->name,
                'uuid' => $this->resource->faction->uuid,
                'link' => $this->urlWithVersion(
                    route('factions.show', ['faction' => $this->resource->faction->uuid]),
                    $request,
                ),
            ] : null,
            'link' => $this->urlWithVersion(
                route('missions.show', ['mission' => $mission?->uuid]),
                $request,
            ),
            'web_url' => $this->urlWithVersion(
                route('web.missions.show', ['mission' => $mission?->slug ?? $mission?->uuid]),
                $request,
            ),
        ];
    }
}
