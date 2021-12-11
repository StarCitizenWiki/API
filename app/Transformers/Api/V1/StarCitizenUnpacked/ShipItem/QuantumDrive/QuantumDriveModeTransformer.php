<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\QuantumDrive;

use App\Models\StarCitizenUnpacked\ShipItem\QuantumDrive\QuantumDriveMode;
use League\Fractal\TransformerAbstract;

class QuantumDriveModeTransformer extends TransformerAbstract
{
    public function transform(QuantumDriveMode $item): array
    {
        return [
            'type' => sprintf('%s_jump', $item->type),
            'drive_speed' => $item->drive_speed,
            'cooldown_time' => $item->cooldown_time,
            'stage_one_accel_rate' => $item->stage_one_accel_rate,
            'stage_two_accel_rate' => $item->stage_two_accel_rate,
            'engage_speed' => $item->engage_speed,
            'interdiction_effect_time' => $item->interdiction_effect_time,
            'calibration_rate' => $item->calibration_rate,
            'min_calibration_requirement' => $item->min_calibration_requirement,
            'max_calibration_requirement' => $item->max_calibration_requirement,
            'calibration_process_angle_limit' => $item->calibration_process_angle_limit,
            'calibration_warning_angle_limit' => $item->calibration_warning_angle_limit,
            'spool_up_time' => $item->spool_up_time,
        ];
    }
}
