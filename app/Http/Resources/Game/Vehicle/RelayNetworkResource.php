<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_relay_network',
    title: 'Relay Network',
    description: 'Ship relay power-distribution topology. Each relay is a fuse-protected junction that feeds connected hardpoints.',
    properties: [
        new OA\Property(property: 'total_fuses', description: 'Total fuse slots across all relays.', type: 'integer', example: 18, nullable: true),
        new OA\Property(
            property: 'relays',
            description: 'Relay junction entries with their connected hardpoints.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_relay_entry'),
            nullable: true,
        ),
        new OA\Property(
            property: 'links',
            description: 'Raw relay-to-hardpoint link edges.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'from', description: 'Source hardpoint (relay).', type: 'string', example: 'hardpoint_relay_bridge'),
                    new OA\Property(property: 'to', description: 'Target hardpoint.', type: 'string', example: 'hardpoint_controller_shield'),
                ],
                type: 'object'
            ),
            nullable: true,
        ),
    ],
    type: 'object'
)]
class RelayNetworkResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_fuses' => Arr::get($this->resource, 'TotalFuses'),
            'relays' => RelayEntryResource::collection(Arr::get($this->resource, 'Relays', [])),
            'links' => collect(Arr::get($this->resource, 'Links', []))
                ->map(fn (array $link) => [
                    'from' => Arr::get($link, 'From'),
                    'to' => Arr::get($link, 'To'),
                ])
                ->all(),
        ];
    }
}
