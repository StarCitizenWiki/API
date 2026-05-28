<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_distortion',
    title: 'Item Distortion',
    description: 'Distortion information from stdItem.Distortion.',
    properties: [
        new OA\Property(property: 'decay_rate', description: 'Rate at which distortion damage recovers per second. Higher values mean faster recovery.', type: 'double', nullable: true, x: ['suffix' => ' /s']),
        new OA\Property(property: 'decay_delay', description: 'Delay in seconds before distortion recovery begins after taking damage.', type: 'double', nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'max', description: 'Maximum distortion pool capacity, total distortion damage the item can absorb before shutting down.', type: 'double', nullable: true),
        new OA\Property(property: 'maximum', description: 'Deprecated: Use max.', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'overload_ratio', description: 'Deprecated: Does not exist in game data.', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'warning_ratio', description: 'Ratio of max at which a distortion warning indicator triggers (e.g. 0.75 = warning at 75%).', type: 'double', nullable: true, x: ['tabulator-formatter' => 'pct']),
        new OA\Property(property: 'recovery_ratio', description: 'Threshold below which distortion must fall before the item reactivates after overload. 0 = immediate recovery.', type: 'double', nullable: true, x: ['tabulator-formatter' => 'pct']),
        new OA\Property(property: 'recovery_time', description: 'Deprecated: Does not exist in game data.', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'power_ratio_at_max_distortion', description: 'Power output ratio at maximum distortion. Currently always 0 (reserved for future use).', type: 'double', nullable: true),
        new OA\Property(property: 'power_change_only_at_max_distortion', description: 'Whether power output only changes when distortion reaches maximum (1 = yes, 0 = gradual).', type: 'integer', nullable: true),
        new OA\Property(property: 'shutdown_time', description: 'Computed duration in seconds the item remains shut down (Maximum / DecayRate + DecayDelay).', type: 'double', nullable: true, x: ['suffix' => ' s']),
    ],
    type: 'object'
)]
/** @param array $resource Raw distortion data from stdItem.Distortion sub-array */
class ItemDistortionResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'decay_rate' => round(Arr::get($this, 'DecayRate', 0), 2),
            'decay_delay' => Arr::get($this, 'DecayDelay'),
            'max' => Arr::get($this, 'Maximum'),
            'maximum' => Arr::get($this, 'Maximum'),  // deprecated: use max
            'overload_ratio' => Arr::get($this, 'OverloadRatio'),
            'warning_ratio' => Arr::get($this, 'WarningRatio'),
            'recovery_ratio' => Arr::get($this, 'RecoveryRatio'),
            'recovery_time' => Arr::get($this, 'RecoveryTime'),
            'power_ratio_at_max_distortion' => Arr::get($this, 'PowerRatioAtMaxDistortion'),
            'power_change_only_at_max_distortion' => Arr::get($this, 'PowerChangeOnlyAtMaxDistortion'),
            'shutdown_time' => round(Arr::get($this, 'ShutdownTime', 0), 2),
        ];
    }
}
