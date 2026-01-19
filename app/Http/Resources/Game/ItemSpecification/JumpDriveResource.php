<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'jump_drive',
    title: 'Jump Drive',
    description: '',
    properties: [
        new OA\Property(property: 'alignment_rate', type: 'double', nullable: true),
        new OA\Property(property: 'alignment_decay_rate', type: 'double', nullable: true),
        new OA\Property(property: 'tuning_rate', type: 'double', nullable: true),
        new OA\Property(property: 'tuning_decay_rate', type: 'double', nullable: true),
        new OA\Property(property: 'fuel_usage_efficiency_multiplier', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class JumpDriveResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $item = Arr::get($data, 'stdItem.JumpDrive', []);

        return [
            'alignment_rate' => Arr::get($item, 'AlignmentRate'),
            'alignment_decay_rate' => Arr::get($item, 'AlignmentDecayRate'),
            'tuning_rate' => Arr::get($item, 'TuningRate'),
            'tuning_decay_rate' => Arr::get($item, 'TuningDecayRate'),
            'fuel_usage_efficiency_multiplier' => Arr::get($item, 'FuelUsageEfficiencyMultiplier'),
        ];
    }
}
