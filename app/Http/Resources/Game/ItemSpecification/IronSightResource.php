<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'iron_sight',
    title: 'Iron Sight',
    description: 'Optic iron sight stats sourced from Item.stdItem.WeaponAttachment.IronSight.',
    properties: [
        new OA\Property(property: 'magnification', description: 'Primary zoom level. Uses WeaponAttachment.Magnification when available.', type: 'number', example: 4, nullable: true),
        new OA\Property(property: 'zoom_scale', description: 'Zoom scale provided by the iron sight (same as magnification in game data).', type: 'number', example: 4, nullable: true),
        new OA\Property(property: 'zoom_time_scale', description: 'Speed multiplier for zooming into ADS.', type: 'number', example: 1.0, nullable: true),
        new OA\Property(
            property: 'zeroing',
            description: 'Zeroing distances supported by the sight.',
            properties: [
                new OA\Property(property: 'default_range', description: 'Default zeroing distance (meters).', type: 'number', example: 0, nullable: true),
                new OA\Property(property: 'max_range', description: 'Maximum zeroing distance supported.', type: 'number', example: 1000, nullable: true),
                new OA\Property(property: 'range_increment', description: 'Step size when adjusting zero.', type: 'number', example: 100, nullable: true),
                new OA\Property(property: 'auto_zeroing_time', description: 'Time to auto-zero if supported.', type: 'number', example: 0, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        // deprecated v2
        new OA\Property(property: 'default_range', type: 'number', nullable: true, deprecated: true),
        new OA\Property(property: 'max_range', type: 'number', nullable: true, deprecated: true),
        new OA\Property(property: 'range_increment', type: 'number', nullable: true, deprecated: true),
        new OA\Property(property: 'auto_zeroing_time', type: 'number', nullable: true, deprecated: true),
    ],
    type: 'object'
)]
class IronSightResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $stdItem = $this->extractStdItem($data);

        $weaponAttachment = Arr::get($stdItem, 'WeaponAttachment', []);
        $ironSight = Arr::get($weaponAttachment, 'IronSight', []);

        $magnification = Arr::get($weaponAttachment, 'Magnification', Arr::get($ironSight, 'ZoomScale'));

        $zeroing = [
            'default_range' => Arr::get($ironSight, 'DefaultRange'),
            'max_range' => Arr::get($ironSight, 'MaxRange'),
            'range_increment' => Arr::get($ironSight, 'RangeIncrement'),
            'auto_zeroing_time' => Arr::get($ironSight, 'AutoZeroingTime'),
        ];

        $zeroing = array_filter($zeroing, static fn ($value) => $value !== null);

        return [
            'magnification' => $magnification,
            'zoom_scale' => Arr::get($ironSight, 'ZoomScale', $magnification),
            'zoom_time_scale' => Arr::get($ironSight, 'ZoomTimeScale'),
            'zeroing' => $zeroing === [] ? null : $zeroing,
            // Deprecated
            'default_range' => Arr::get($ironSight, 'DefaultRange'),
            'max_range' => Arr::get($ironSight, 'MaxRange'),
            'range_increment' => Arr::get($ironSight, 'RangeIncrement'),
            'auto_zeroing_time' => Arr::get($ironSight, 'AutoZeroingTime'),
        ];
    }
}
