<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use App\Http\Resources\Game\Concerns\ExtractsJsonData;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'iron_sight',
    title: 'Iron Sight',
    description: 'Deprecated: Use weapon_modifiers',
    properties: [
        new OA\Property(property: 'magnification', type: 'number', nullable: true),
        new OA\Property(property: 'optic_type', type: 'string', nullable: true),
        new OA\Property(property: 'default_range', type: 'number', nullable: true),
        new OA\Property(property: 'max_range', type: 'number', nullable: true),
        new OA\Property(property: 'range_increment', type: 'number', nullable: true),
        new OA\Property(property: 'auto_zeroing_time', type: 'number', nullable: true),
        new OA\Property(property: 'zoom_time_scale', type: 'number', nullable: true),
    ],
    type: 'object', deprecated: true
)]
class IronSightResource extends AbstractItemSpecificationResource
{
    use ExtractsJsonData;

    public function toArray(Request $request): array
    {
        $weaponAttachment = $this->extractFromStdItem($this->resource, 'WeaponAttachment');
        $ironSight = Arr::get($weaponAttachment, 'IronSight', []);

        $magnification = Arr::get($weaponAttachment, 'Magnification', Arr::get($ironSight, 'ZoomScale'));

        return [
            'magnification' => $magnification,
            'optic_type' => Arr::get($ironSight, 'ScopeType'),
            'default_range' => Arr::get($ironSight, 'DefaultRange'),
            'max_range' => Arr::get($ironSight, 'MaxRange'),
            'range_increment' => Arr::get($ironSight, 'RangeIncrement'),
            'auto_zeroing_time' => Arr::get($ironSight, 'AutoZeroingTime'),
            'zoom_time_scale' => Arr::get($ironSight, 'ZoomTimeScale'),
        ];
    }
}
