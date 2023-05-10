<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification\QuantumDrive;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'quantum_drive_modes_v2',
    title: 'Quantum Drive Modes',
    properties: [
        new OA\Property(property: 'type', type: 'string', nullable: true),
        new OA\Property(property: 'drive_speed', type: 'double', nullable: true),
        new OA\Property(property: 'cooldown_time', type: 'double', nullable: true),
        new OA\Property(property: 'stage_one_accel_rate', type: 'double', nullable: true),
        new OA\Property(property: 'stage_two_accel_rate', type: 'double', nullable: true),
        new OA\Property(property: 'engage_speed', type: 'double', nullable: true),
        new OA\Property(property: 'interdiction_effect_time', type: 'double', nullable: true),
        new OA\Property(property: 'calibration_rate', type: 'double', nullable: true),
        new OA\Property(property: 'min_calibration_requirement', type: 'double', nullable: true),
        new OA\Property(property: 'max_calibration_requirement', type: 'double', nullable: true),
        new OA\Property(property: 'calibration_process_angle_limit', type: 'double', nullable: true),
        new OA\Property(property: 'calibration_warning_angle_limit', type: 'double', nullable: true),
        new OA\Property(property: 'spool_up_time', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class QuantumDriveModeResource extends AbstractBaseResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'type' => sprintf('%s_jump', $this->type),
            'drive_speed' => $this->drive_speed,
            'cooldown_time' => $this->cooldown_time,
            'stage_one_accel_rate' => $this->stage_one_accel_rate,
            'stage_two_accel_rate' => $this->stage_two_accel_rate,
            'engage_speed' => $this->engage_speed,
            'interdiction_effect_time' => $this->interdiction_effect_time,
            'calibration_rate' => $this->calibration_rate,
            'min_calibration_requirement' => $this->min_calibration_requirement,
            'max_calibration_requirement' => $this->max_calibration_requirement,
            'calibration_process_angle_limit' => $this->calibration_process_angle_limit,
            'calibration_warning_angle_limit' => $this->calibration_warning_angle_limit,
            'spool_up_time' => $this->spool_up_time,
        ];
    }
}
