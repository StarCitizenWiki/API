<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'quantum_drive_jump_profile',
    title: 'Quantum Drive Jump Profile',
    description: 'Jump profile settings for quantum travel modes (StandardJump/SplineJump). Values mirror stdItem.QuantumDrive.[StandardJump|SplineJump] in game data.',
    properties: [
        new OA\Property(
            property: 'drive_speed',
            description: 'Max cruise velocity for the jump profile (m/s). Standard jumps range ~138,000,000-876,000,000; spline jumps ~400,000-500,000.',
            type: 'double',
            example: 218000000,
            nullable: true
        ),
        new OA\Property(
            property: 'cooldown_time',
            description: 'Cooldown after exit in seconds; dataset ranges 0-92.07.',
            type: 'double',
            example: 41.4,
            nullable: true
        ),
        new OA\Property(
            property: 'stage_one_accel_rate',
            description: 'Initial acceleration phase (m/s²). Standard ~1.75M; spline as low as 250.',
            type: 'double',
            example: 1750000,
            nullable: true
        ),
        new OA\Property(
            property: 'stage_two_accel_rate',
            description: 'Secondary acceleration phase (m/s²). Standard up to 11,000,000; spline around 50,000.',
            type: 'double',
            example: 11000000,
            nullable: true
        ),
        new OA\Property(
            property: 'engage_speed',
            description: 'Minimum ship speed to engage QT (m/s).',
            type: 'double',
            example: 1500,
            nullable: true
        ),
        new OA\Property(
            property: 'interdiction_effect_time',
            description: 'Duration of interdiction effect (seconds).',
            type: 'double',
            example: 5,
            nullable: true
        ),
        new OA\Property(
            property: 'calibration_rate',
            description: 'Calibration progress per second.',
            type: 'double',
            example: 1000,
            nullable: true
        ),
        new OA\Property(
            property: 'min_calibration_requirement',
            description: 'Minimum calibration distance gate.',
            type: 'double',
            example: 5000,
            nullable: true
        ),
        new OA\Property(
            property: 'max_calibration_requirement',
            description: 'Maximum calibration distance gate.',
            type: 'double',
            example: 10000,
            nullable: true
        ),
        new OA\Property(
            property: 'calibration_process_angle_limit',
            description: 'Angular tolerance during calibration (degrees).',
            type: 'double',
            example: 5,
            nullable: true
        ),
        new OA\Property(
            property: 'calibration_warning_angle_limit',
            description: 'Warning threshold for calibration cone (degrees).',
            type: 'double',
            example: 8,
            nullable: true
        ),
        new OA\Property(
            property: 'calibration_delay_in_seconds',
            description: 'Delay applied before calibration proceeds (seconds).',
            type: 'double',
            example: 1.5,
            nullable: true
        ),
        new OA\Property(
            property: 'spool_up_time',
            description: 'Time to spool from idle to ready (seconds).',
            type: 'double',
            example: 6,
            nullable: true
        ),
    ],
    type: 'object'
)]
class QuantumDriveJumpProfileResource extends AbstractBaseResource
{
    public function __construct($resource, private readonly ?string $type = null)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $profile = is_array($this->resource) ? $this->resource : [];

        return [
            $this->mergeWhen($this->type !== null, [
                'type' => $this->type,
            ]),
            'drive_speed' => Arr::get($profile, 'DriveSpeed'),
            'cooldown_time' => Arr::get($profile, 'CooldownTime'),
            'stage_one_accel_rate' => Arr::get($profile, 'StageOneAccelRate'),
            'stage_two_accel_rate' => Arr::get($profile, 'StageTwoAccelRate'),
            'engage_speed' => Arr::get($profile, 'EngageSpeed'),
            'interdiction_effect_time' => Arr::get($profile, 'InterdictionEffectTime'),
            'calibration_rate' => Arr::get($profile, 'CalibrationRate'),
            'min_calibration_requirement' => Arr::get($profile, 'MinCalibrationRequirement'),
            'max_calibration_requirement' => Arr::get($profile, 'MaxCalibrationRequirement'),
            'calibration_process_angle_limit' => Arr::get($profile, 'CalibrationProcessAngleLimit'),
            'calibration_warning_angle_limit' => Arr::get($profile, 'CalibrationWarningAngleLimit'),
            'calibration_delay_in_seconds' => Arr::get($profile, 'CalibrationDelayInSeconds'),
            'spool_up_time' => Arr::get($profile, 'SpoolUpTime'),
        ];
    }
}
