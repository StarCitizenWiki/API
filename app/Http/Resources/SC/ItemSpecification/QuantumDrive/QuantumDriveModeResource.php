<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification\QuantumDrive;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;

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
