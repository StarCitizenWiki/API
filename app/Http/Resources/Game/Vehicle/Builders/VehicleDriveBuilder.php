<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle\Builders;

use Illuminate\Support\Arr;

/**
 * @internal
 *
 * Pure-function pipeline for building ground vehicle drive characteristics.
 */
final class VehicleDriveBuilder
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    public function buildDriveCharacteristics(array $payload): ?array
    {
        $dc = Arr::get($payload, 'DriveCharacteristics', []);
        $speed = Arr::get($dc, 'Speed');

        // Backwards compat <= 4.7.2
        $maxSpeedKph = Arr::get($speed, 'WheelMaxSpeedKph')
            ?? Arr::get($speed, 'TopSpeedKph')
            ?? Arr::get($speed, 'TrackMaxSpeedKph');

        $maxSpeedMs = Arr::get($speed, 'WheelMaxSpeedMs')
            ?? Arr::get($speed, 'TopSpeedMs')
            ?? Arr::get($speed, 'TrackMaxSpeedMs');

        $reverseSpeedKph = Arr::get($speed, 'ReverseSpeedKph');
        $reverseSpeedMs = Arr::get($speed, 'ReverseSpeedMs');

        $wheels = Arr::get($dc, 'Wheels');
        $agility = Arr::get($dc, 'Agility');

        $isTracked = Arr::get($dc, 'IsTracked')
            ?? Arr::get($dc, 'Tracks.IsTracked');

        $driveCharacteristics = array_filter([
            'max_speed_kph' => $maxSpeedKph,
            'max_speed_ms' => $maxSpeedMs,
            'reverse_speed_kph' => $reverseSpeedKph,
            'reverse_speed_ms' => $reverseSpeedMs,
            'is_tracked' => $isTracked,
            'wheels' => is_array($wheels) ? array_filter([
                'count' => Arr::get($wheels, 'Count'),
                'driving_count' => Arr::get($wheels, 'DrivingCount'),
                'steering_count' => Arr::get($wheels, 'SteeringCount'),
                'drive_type' => Arr::get($wheels, 'DriveType'),
            ], static fn (mixed $value): bool => $value !== null) : null,
            'agility' => is_array($agility) ? array_filter([
                'handling' => Arr::get($agility, 'HandlingScore'),
                'grip' => Arr::get($agility, 'GripScore'),
                'acceleration' => Arr::get($agility, 'AccelerationScore'),
            ], static fn (mixed $value): bool => $value !== null) : null,
        ], static fn (mixed $value): bool => $value !== null);

        $stanceSpeed = $this->buildStanceSpeed($payload);

        if ($stanceSpeed !== null) {
            $driveCharacteristics['stance_speed'] = $stanceSpeed;
        }

        return $driveCharacteristics !== [] ? $driveCharacteristics : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    public function buildStanceSpeed(array $payload): ?array
    {
        $raw = Arr::get($payload, 'StanceSpeed');

        if (! is_array($raw)) {
            return null;
        }

        $sprintKph = Arr::get($raw, 'SprintSpeedKph');

        return array_filter([
            'walk_kph' => Arr::get($raw, 'WalkSpeedKph'),
            'sprint_kph' => $sprintKph > 0 ? $sprintKph : null,
            'acceleration' => Arr::get($raw, 'Acceleration'),
            'rotation_speed' => Arr::get($raw, 'RotationSpeed'),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
