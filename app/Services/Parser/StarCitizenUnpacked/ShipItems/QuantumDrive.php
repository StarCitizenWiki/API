<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class QuantumDrive extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::get($item, 'SCItemQuantumDriveParams');

        if ($data === null) {
            return null;
        }

        $modes = [
            'normal' => array_filter([
                'drive_speed' => Arr::get($data, 'params.driveSpeed'),
                'cooldown_time' => Arr::get($data, 'params.cooldownTime'),
                'stage_one_accel_rate' => Arr::get($data, 'params.stageOneAccelRate'),
                'stage_two_accel_rate' => Arr::get($data, 'params.stageTwoAccelRate'),
                'engage_speed' => Arr::get($data, 'params.engageSpeed'),
                'interdiction_effect_time' => Arr::get($data, 'params.interdictionEffectTime'),
                'calibration_rate' => Arr::get($data, 'params.calibrationRate'),
                'min_calibration_requirement' => Arr::get($data, 'params.minCalibrationRequirement'),
                'max_calibration_requirement' => Arr::get($data, 'params.maxCalibrationRequirement'),
                'calibration_process_angle_limit' => Arr::get($data, 'params.calibrationProcessAngleLimit'),
                'calibration_warning_angle_limit' => Arr::get($data, 'params.calibrationWarningAngleLimit'),
                'calibration_delay_in_seconds' => Arr::get($data, 'params.calibrationDelayInSeconds'),
                'spool_up_time' => Arr::get($data, 'params.spoolUpTime'),
            ]),
            'spline' => array_filter([
                'drive_speed' => Arr::get($data, 'splineJumpParams.driveSpeed'),
                'cooldown_time' => Arr::get($data, 'splineJumpParams.cooldownTime'),
                'stage_one_accel_rate' => Arr::get($data, 'splineJumpParams.stageOneAccelRate'),
                'stage_two_accel_rate' => Arr::get($data, 'splineJumpParams.stageTwoAccelRate'),
                'engage_speed' => Arr::get($data, 'splineJumpParams.engageSpeed'),
                'interdiction_effect_time' => Arr::get($data, 'splineJumpParams.interdictionEffectTime'),
                'calibration_rate' => Arr::get($data, 'splineJumpParams.calibrationRate'),
                'min_calibration_requirement' => Arr::get($data, 'splineJumpParams.minCalibrationRequirement'),
                'max_calibration_requirement' => Arr::get($data, 'splineJumpParams.maxCalibrationRequirement'),
                'calibration_process_angle_limit' => Arr::get($data, 'splineJumpParams.calibrationProcessAngleLimit'),
                'calibration_warning_angle_limit' => Arr::get($data, 'splineJumpParams.calibrationWarningAngleLimit'),
                'calibration_delay_in_seconds' => Arr::get($data, 'splineJumpParams.calibrationDelayInSeconds'),
                'spool_up_time' => Arr::get($data, 'splineJumpParams.spoolUpTime'),
            ]),
        ];

        return array_filter([
            'quantum_fuel_requirement' => Arr::get($data, 'quantumFuelRequirement'),
            'jump_range' => Arr::get($data, 'jumpRange'),
            'disconnect_range' => Arr::get($data, 'disconnectRange'),

            'pre_ramp_up_thermal_energy_draw' => Arr::get($data, 'heatParams.preRampUpThermalEnergyDraw'),
            'ramp_up_thermal_energy_draw' => Arr::get($data, 'heatParams.rampUpThermalEnergyDraw'),
            'in_flight_thermal_energy_draw' => Arr::get($data, 'heatParams.inFlightThermalEnergyDraw'),
            'ramp_down_thermal_energy_draw' => Arr::get($data, 'heatParams.rampDownThermalEnergyDraw'),
            'post_ramp_down_thermal_energy_draw' => Arr::get($data, 'heatParams.postRampDownThermalEnergyDraw'),

            'modes' => array_filter($modes),
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
