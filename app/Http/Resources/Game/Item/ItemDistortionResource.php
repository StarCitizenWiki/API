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
        new OA\Property(property: 'decay_rate', type: 'double', nullable: true),
        new OA\Property(property: 'decay_delay', type: 'double', nullable: true),
        new OA\Property(property: 'maximum', type: 'double', nullable: true),
        new OA\Property(property: 'overload_ratio', description: 'Deprecated, does not exist anymore.', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'warning_ratio', type: 'double', nullable: true),
        new OA\Property(property: 'recovery_ratio', type: 'double', nullable: true),
        new OA\Property(property: 'recovery_time', description: 'Deprecated, does not exist anymore.', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'power_ratio_at_max_distortion', type: 'double', nullable: true),
        new OA\Property(property: 'power_change_only_at_max_distortion', type: 'integer', nullable: true),
        new OA\Property(property: 'shutdown_time', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class ItemDistortionResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'decay_rate' => Arr::get($this, 'DecayRate'),
            'decay_delay' => Arr::get($this, 'DecayDelay'),
            'maximum' => Arr::get($this, 'Maximum'),
            'overload_ratio' => Arr::get($this, 'OverloadRatio'),
            'warning_ratio' => Arr::get($this, 'WarningRatio'),
            'recovery_ratio' => Arr::get($this, 'RecoveryRatio'),
            'recovery_time' => Arr::get($this, 'RecoveryTime'),
            'power_ratio_at_max_distortion' => Arr::get($this, 'PowerRatioAtMaxDistortion'),
            'power_change_only_at_max_distortion' => Arr::get($this, 'PowerChangeOnlyAtMaxDistortion'),
            'shutdown_time' => Arr::get($this, 'ShutdownTime'),
        ];
    }
}
