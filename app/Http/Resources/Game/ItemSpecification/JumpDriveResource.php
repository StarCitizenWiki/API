<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'jump_drive',
    title: 'Jump Drive',
    description: 'Jump drive alignment, tuning, and fuel efficiency stats sourced from SCItemJumpDriveParams.',
    properties: [
        new OA\Property(property: 'alignment_rate', description: 'Rate at which the drive aligns to a jump point. Higher values mean faster alignment.', type: 'double', nullable: true),
        new OA\Property(property: 'alignment_decay_rate', description: 'Rate at which alignment decays when not actively aligning.', type: 'double', nullable: true),
        new OA\Property(property: 'tuning_rate', description: 'Tuning speed. Higher values mean faster calibration.', type: 'double', nullable: true),
        new OA\Property(property: 'tuning_decay_rate', description: 'Rate at which tuning decays when not actively tuning.', type: 'double', nullable: true),
        new OA\Property(property: 'fuel_usage_efficiency_multiplier', description: 'Fuel efficiency multiplier. Higher values mean less fuel consumed per jump.', type: 'double', nullable: true),
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
