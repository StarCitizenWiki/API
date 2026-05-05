<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle\Builders;

use Illuminate\Support\Arr;

/**
 * @internal
 *
 * Pure-function pipeline for building flight-related vehicle data:
 * speed, agility, fuel, propulsion, and quantum travel.
 */
final class VehicleFlightBuilder
{
    /**
     * @param  array<string, mixed>  $flight
     * @return array<string, mixed>
     */
    public function buildSpeed(array $flight): array
    {
        return [
            'scm' => Arr::get($flight, 'Speeds.Scm'),
            'max' => Arr::get($flight, 'Speeds.Max'),
            'boost_forward' => Arr::get($flight, 'Speeds.BoostForward'),
            'boost_backward' => Arr::get($flight, 'Speeds.BoostBackward'),
            'zero_to_scm' => Arr::get($flight, 'Timing.ZeroToScm'),
            'zero_to_max' => Arr::get($flight, 'Timing.ZeroToMax'),
            'scm_to_zero' => Arr::get($flight, 'Timing.ScmToZero'),
            'max_to_zero' => Arr::get($flight, 'Timing.MaxToZero'),
        ];
    }

    /**
     * @param  array<string, mixed>  $flight
     * @return array<string, mixed>
     */
    public function buildAgility(array $flight): array
    {
        return [
            'pitch' => Arr::get($flight, 'AngularRates.Pitch'),
            'yaw' => Arr::get($flight, 'AngularRates.Yaw'),
            'roll' => Arr::get($flight, 'AngularRates.Roll'),
            'pitch_boosted' => Arr::get($flight, 'AngularRatesBoosted.Pitch'),
            'yaw_boosted' => Arr::get($flight, 'AngularRatesBoosted.Yaw'),
            'roll_boosted' => Arr::get($flight, 'AngularRatesBoosted.Roll'),

            'acceleration' => array_filter([
                'main' => Arr::get($flight, 'Acceleration.Raw.Forward'),
                'retro' => Arr::get($flight, 'Acceleration.Raw.Backward'),
                'vtol' => Arr::get($flight, 'Acceleration.Raw.Vtol'),
                'maneuvering' => Arr::get($flight, 'Acceleration.Raw.Maneuvering'),

                'main_g' => Arr::get($flight, 'Acceleration.RawG.Forward'),
                'retro_g' => Arr::get($flight, 'Acceleration.RawG.Backward'),
                'vtol_g' => Arr::get($flight, 'Acceleration.RawG.Vtol'),
                'maneuvering_g' => Arr::get($flight, 'Acceleration.RawG.Maneuvering'),
            ], static fn ($value) => $value !== null),
        ];
    }

    /**
     * @param  array<string, mixed>  $propulsion
     * @return array<string, mixed>
     */
    public function buildFuel(array $propulsion): array
    {
        $usage = Arr::get($propulsion, 'FuelUsage', []);

        return [
            'capacity' => (float) (Arr::get($propulsion, 'FuelCapacity', 0)) / 1000,
            'intake_rate' => Arr::get($propulsion, 'FuelIntakeRate'),
            'usage' => [
                'main' => Arr::get($usage, 'Main'),
                'retro' => Arr::get($usage, 'Retro'),
                'vtol' => Arr::get($usage, 'Vtol'),
                'maneuvering' => Arr::get($usage, 'Maneuvering'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $propulsion
     * @return array<string, mixed>
     */
    public function buildPropulsion(array $propulsion): array
    {
        $thrusters = Arr::get($propulsion, 'Thrusters', []);
        $thrusters = is_array($thrusters) ? $thrusters : [];
        $thrustCapacityRaw = Arr::get($propulsion, 'ThrustCapacity');
        $thrustCapacity = null;
        if (is_array($thrustCapacityRaw)) {
            $thrustCapacity = array_filter([
                'main' => Arr::get($thrustCapacityRaw, 'Main'),
                'retro' => Arr::get($thrustCapacityRaw, 'Retro'),
                'vtol' => Arr::get($thrustCapacityRaw, 'Vtol'),
                'maneuvering' => Arr::get($thrustCapacityRaw, 'Maneuvering'),
            ], static fn (mixed $value): bool => $value !== null);

            if ($thrustCapacity === []) {
                $thrustCapacity = null;
            }
        }

        return [
            'thrusters' => array_map(static fn (array $thruster): array => [
                'type' => Arr::get($thruster, 'Type'),
                'count' => Arr::get($thruster, 'Count'),
                'capacity' => Arr::get($thruster, 'Capacity'),
                'g' => Arr::get($thruster, 'G'),
            ], $thrusters),
            'thrust_capacity' => $thrustCapacity,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function buildQuantum(array $payload): array
    {
        $qt = Arr::get($payload, 'QuantumTravel', []);

        return [
            'quantum_speed' => Arr::get($qt, 'Speed'),
            'quantum_spool_time' => Arr::get($qt, 'SpoolTime'),
            'quantum_fuel_capacity' => (float) (Arr::get($qt, 'FuelCapacity', 0)) / 1000,
            'quantum_range' => Arr::get($qt, 'Range'),
            'port_olisar_to_arccorp_time' => Arr::get($qt, 'PortOlisarToArcCorpTime'),
            'port_olisar_to_arccorp_fuel' => Arr::get($qt, 'PortOlisarToArcCorpFuel'),
        ];
    }
}
