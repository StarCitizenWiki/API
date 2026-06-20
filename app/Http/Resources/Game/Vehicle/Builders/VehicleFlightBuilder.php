<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle\Builders;

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
        $speeds = $flight['Speeds'] ?? [];
        $timing = $flight['Timing'] ?? [];

        return [
            'scm' => $speeds['Scm'] ?? null,
            'max' => $speeds['Max'] ?? null,
            'boost_forward' => $speeds['BoostForward'] ?? null,
            'boost_backward' => $speeds['BoostBackward'] ?? null,
            'zero_to_scm' => $timing['ZeroToScm'] ?? null,
            'zero_to_max' => $timing['ZeroToMax'] ?? null,
            'scm_to_zero' => $timing['ScmToZero'] ?? null,
            'max_to_zero' => $timing['MaxToZero'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $flight
     * @return array<string, mixed>
     */
    public function buildAgility(array $flight): array
    {
        $rates = $flight['AngularRates'] ?? [];
        $ratesBoosted = $flight['AngularRatesBoosted'] ?? [];
        $accRaw = $flight['Acceleration']['Raw'] ?? [];
        $accRawG = $flight['Acceleration']['RawG'] ?? [];

        return [
            'pitch' => $rates['Pitch'] ?? null,
            'yaw' => $rates['Yaw'] ?? null,
            'roll' => $rates['Roll'] ?? null,
            'pitch_boosted' => $ratesBoosted['Pitch'] ?? null,
            'yaw_boosted' => $ratesBoosted['Yaw'] ?? null,
            'roll_boosted' => $ratesBoosted['Roll'] ?? null,

            'acceleration' => array_filter([
                'main' => $accRaw['Forward'] ?? null,
                'retro' => $accRaw['Backward'] ?? null,
                'vtol' => $accRaw['Vtol'] ?? null,
                'maneuvering' => $accRaw['Maneuvering'] ?? null,

                'main_g' => $accRawG['Forward'] ?? null,
                'retro_g' => $accRawG['Backward'] ?? null,
                'vtol_g' => $accRawG['Vtol'] ?? null,
                'maneuvering_g' => $accRawG['Maneuvering'] ?? null,
            ], static fn ($value) => $value !== null),
        ];
    }

    /**
     * @param  array<string, mixed>  $propulsion
     * @return array<string, mixed>
     */
    public function buildFuel(array $propulsion): array
    {
        $usage = $propulsion['FuelUsage'] ?? [];

        return [
            'capacity' => (float) ($propulsion['FuelCapacity'] ?? 0) / 1000,
            'intake_rate' => $propulsion['FuelIntakeRate'] ?? null,
            'usage' => [
                'main' => $usage['Main'] ?? null,
                'retro' => $usage['Retro'] ?? null,
                'vtol' => $usage['Vtol'] ?? null,
                'maneuvering' => $usage['Maneuvering'] ?? null,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $propulsion
     * @return array<string, mixed>
     */
    public function buildPropulsion(array $propulsion): array
    {
        $thrusters = $propulsion['Thrusters'] ?? [];
        $thrusters = is_array($thrusters) ? $thrusters : [];
        $thrustCapacityRaw = $propulsion['ThrustCapacity'] ?? null;
        $thrustCapacity = null;
        if (is_array($thrustCapacityRaw)) {
            $thrustCapacity = array_filter([
                'main' => $thrustCapacityRaw['Main'] ?? null,
                'retro' => $thrustCapacityRaw['Retro'] ?? null,
                'vtol' => $thrustCapacityRaw['Vtol'] ?? null,
                'maneuvering' => $thrustCapacityRaw['Maneuvering'] ?? null,
            ], static fn (mixed $value): bool => $value !== null);

            if ($thrustCapacity === []) {
                $thrustCapacity = null;
            }
        }

        return [
            'thrusters' => array_map(static fn (array $thruster): array => [
                'type' => $thruster['Type'] ?? null,
                'count' => $thruster['Count'] ?? null,
                'capacity' => $thruster['Capacity'] ?? null,
                'g' => $thruster['G'] ?? null,
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
        $qt = $payload['QuantumTravel'] ?? [];

        return [
            'quantum_speed' => $qt['Speed'] ?? null,
            'quantum_spool_time' => $qt['SpoolTime'] ?? null,
            'quantum_fuel_capacity' => (float) ($qt['FuelCapacity'] ?? 0) / 1000,
            'quantum_range' => $qt['Range'] ?? null,
            'port_olisar_to_arccorp_time' => $qt['PortOlisarToArcCorpTime'] ?? null,
            'port_olisar_to_arccorp_fuel' => $qt['PortOlisarToArcCorpFuel'] ?? null,
        ];
    }
}
