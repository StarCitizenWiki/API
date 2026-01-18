<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_power_connection',
    title: 'Item Power Connection',
    description: 'Power connection information, generated from EntityComponentPowerConnection attributes.',
    properties: [
        new OA\Property(property: 'power_base', type: 'double', nullable: true),
        new OA\Property(property: 'power_draw', type: 'double', nullable: true),
        new OA\Property(property: 'throttleable', description: 'IsThrottleable', type: 'boolean', nullable: true),
        new OA\Property(property: 'overclockable', description: 'IsOverclockable', type: 'boolean', nullable: true),
        new OA\Property(property: 'overclock_threshold_min', type: 'double', nullable: true),
        new OA\Property(property: 'overclock_threshold_max', type: 'double', nullable: true),
        new OA\Property(property: 'overclock_performance', type: 'double', nullable: true),
        new OA\Property(property: 'overpower_performance', type: 'double', nullable: true),
        new OA\Property(property: 'power_to_em', type: 'double', nullable: true),
        new OA\Property(property: 'decay_rate_em', type: 'double', nullable: true),
        new OA\Property(property: 'em_min', description: 'PowerBase * PowerToEm. Use ResourceNetwork data instead.', type: 'double', nullable: true),
        new OA\Property(property: 'em_max', description: 'PowerDraw * PowerToEm. Use ResourceNetwork data instead.', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class ItemPowerConnectionResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'power_base' => Arr::get($this, 'PowerBase'),
            'power_draw' => Arr::get($this, 'PowerDraw'),
            'throttleable' => Arr::get($this, 'IsThrottleable'),
            'overclockable' => Arr::get($this, 'IsOverclockable'),
            'overclock_threshold_min' => Arr::get($this, 'OverclockThresholdMin'),
            'overclock_threshold_max' => Arr::get($this, 'OverclockThresholdMax'),
            'overpower_performance' => Arr::get($this, 'OverpowerPerformance'),
            'overclock_performance' => Arr::get($this, 'OverclockPerformance'),
            'power_to_em' => Arr::get($this, 'PowerToEM'),
            'decay_rate_em' => Arr::get($this, 'DecayRateOfEM'),
            'em_min' => Arr::get($this, 'PowerBase', 0) * Arr::get($this, 'PowerToEM', 0),
            'em_max' => Arr::get($this, 'PowerDraw', 0) * Arr::get($this, 'PowerToEM', 0),
        ];
    }
}
