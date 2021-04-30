<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

final class QuantumDrive extends AbstractItemSpecification
{
    public static function getData(array $item, Collection $rawData): ?array
    {
        if (!isset($rawData['Components']['SCItemQuantumDriveParams'])) {
            return null;
        }

        $basePath = 'Components.SCItemQuantumDriveParams.';

        $modes = [
            'normal' => array_filter([
                'drive_speed' => $rawData->pull($basePath . 'params.driveSpeed'),
                'cooldown_time' => $rawData->pull($basePath . 'params.cooldownTime'),
                'stage_one_accel_rate' => $rawData->pull($basePath . 'params.stageOneAccelRate'),
                'stage_two_accel_rate' => $rawData->pull($basePath . 'params.stageTwoAccelRate'),
                'engage_speed' => $rawData->pull($basePath . 'params.engageSpeed'),
                'interdiction_effect_time' => $rawData->pull($basePath . 'params.interdictionEffectTime'),
                'calibration_rate' => $rawData->pull($basePath . 'params.calibrationRate'),
                'min_calibration_requirement' => $rawData->pull($basePath . 'params.minCalibrationRequirement'),
                'max_calibration_requirement' => $rawData->pull($basePath . 'params.maxCalibrationRequirement'),
                'calibration_process_angle_limit' => $rawData->pull($basePath . 'params.calibrationProcessAngleLimit'),
                'calibration_warning_angle_limit' => $rawData->pull($basePath . 'params.calibrationWarningAngleLimit'),
                'calibration_delay_in_seconds' => $rawData->pull($basePath . 'params.calibrationDelayInSeconds'),
                'spool_up_time' => $rawData->pull($basePath . 'params.spoolUpTime'),
            ]),
            'spline' => array_filter([
                'drive_speed' => $rawData->pull($basePath . 'splineJumpParams.driveSpeed'),
                'cooldown_time' => $rawData->pull($basePath . 'splineJumpParams.cooldownTime'),
                'stage_one_accel_rate' => $rawData->pull($basePath . 'splineJumpParams.stageOneAccelRate'),
                'stage_two_accel_rate' => $rawData->pull($basePath . 'splineJumpParams.stageTwoAccelRate'),
                'engage_speed' => $rawData->pull($basePath . 'splineJumpParams.engageSpeed'),
                'interdiction_effect_time' => $rawData->pull($basePath . 'splineJumpParams.interdictionEffectTime'),
                'calibration_rate' => $rawData->pull($basePath . 'splineJumpParams.calibrationRate'),
                'min_calibration_requirement' => $rawData->pull($basePath . 'splineJumpParams.minCalibrationRequirement'),
                'max_calibration_requirement' => $rawData->pull($basePath . 'splineJumpParams.maxCalibrationRequirement'),
                'calibration_process_angle_limit' => $rawData->pull($basePath . 'splineJumpParams.calibrationProcessAngleLimit'),
                'calibration_warning_angle_limit' => $rawData->pull($basePath . 'splineJumpParams.calibrationWarningAngleLimit'),
                'calibration_delay_in_seconds' => $rawData->pull($basePath . 'splineJumpParams.calibrationDelayInSeconds'),
                'spool_up_time' => $rawData->pull($basePath . 'splineJumpParams.spoolUpTime'),
            ]),
        ];

        return array_filter([
            'quantum_fuel_requirement' => $rawData->pull($basePath . 'quantumFuelRequirement'),
            'jump_range' => $rawData->pull($basePath . 'jumpRange'),
            'disconnect_range' => $rawData->pull($basePath . 'disconnectRange'),

            'pre_ramp_up_thermal_energy_draw' => $rawData->pull($basePath . 'heatParams.preRampUpThermalEnergyDraw'),
            'ramp_up_thermal_energy_draw' => $rawData->pull($basePath . 'heatParams.rampUpThermalEnergyDraw'),
            'in_flight_thermal_energy_draw' => $rawData->pull($basePath . 'heatParams.inFlightThermalEnergyDraw'),
            'ramp_down_thermal_energy_draw' => $rawData->pull($basePath . 'heatParams.rampDownThermalEnergyDraw'),
            'post_ramp_down_thermal_energy_draw' => $rawData->pull($basePath . 'heatParams.postRampDownThermalEnergyDraw'),

            'modes' => array_filter($modes),
        ], static function ($entry) {
            return $entry !== null && !empty($entry);
        });
    }
}
