<?php

declare(strict_types=1);

use App\Http\Resources\Game\ItemSpecification\QuantumDriveResource;
use Illuminate\Http\Request;

it('maps quantum drive data from stdItem only', function () {
    $payload = [
        'data' => [
            'stdItem' => [
                'QuantumDrive' => [
                    'JumpRange' => 3.402823e+38,
                    'DisconnectRange' => 34693,
                    'QuantumFuelRequirement' => 0.006758,
                    'FuelRate' => 6.758e-9,
                    'Heat' => [
                        'PreRampUpThermalEnergyDraw' => 8700,
                        'RampUpThermalEnergyDraw' => 8700,
                        'InFlightThermalEnergyDraw' => 8700,
                        'RampDownThermalEnergyDraw' => 8700,
                        'PostRampDownThermalEnergyDraw' => 8700,
                    ],
                    'StandardJump' => [
                        'DriveSpeed' => 218000000,
                        'CooldownTime' => 41.4,
                        'StageOneAccelRate' => 1750000,
                        'StageTwoAccelRate' => 11000000,
                        'EngageSpeed' => 1500,
                        'InterdictionEffectTime' => 5,
                        'CalibrationRate' => 1000,
                        'MinCalibrationRequirement' => 5000,
                        'MaxCalibrationRequirement' => 10000,
                        'CalibrationProcessAngleLimit' => 5,
                        'CalibrationWarningAngleLimit' => 8,
                        'CalibrationDelayInSeconds' => 1.5,
                        'SpoolUpTime' => 6,
                    ],
                    'SplineJump' => [
                        'DriveSpeed' => 400000,
                        'CooldownTime' => 41.4,
                        'StageOneAccelRate' => 250,
                        'StageTwoAccelRate' => 50000,
                        'EngageSpeed' => 1500,
                        'InterdictionEffectTime' => 5,
                        'CalibrationRate' => 1000,
                        'MinCalibrationRequirement' => 5000,
                        'MaxCalibrationRequirement' => 10000,
                        'CalibrationProcessAngleLimit' => 5,
                        'CalibrationWarningAngleLimit' => 8,
                        'CalibrationDelayInSeconds' => 1.5,
                        'SpoolUpTime' => 6,
                    ],
                ],
            ],
        ],
    ];

    $resource = new QuantumDriveResource($payload);

    $data = $resource->toArray(new Request);

    expect($data['quantum_fuel_requirement'])->toBe(0.006758)
        ->and($data['fuel_rate'])->toBe(6.758e-9)
        ->and($data['jump_range'])->toBe(3.402823e+38)
        ->and($data['disconnect_range'])->toBe(34693)
        ->and($data['heat']['pre_ramp_up_thermal_energy_draw'])->toBe(8700)
        ->and($data['standard_jump']['drive_speed'])->toBe(218000000)
        ->and($data['standard_jump']['calibration_delay_in_seconds'])->toBe(1.5)
        ->and($data['spline_jump']['drive_speed'])->toBe(400000)
        ->and($data['spline_jump']['stage_two_accel_rate'])->toBe(50000);
});
