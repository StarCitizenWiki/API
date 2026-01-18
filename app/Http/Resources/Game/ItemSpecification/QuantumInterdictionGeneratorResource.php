<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'quantum_interdiction_generator',
    title: 'Quantum Interdiction Generator',
    description: 'Gameplay-facing interdiction and jamming stats sourced from stdItem.QuantumInterdiction in the game data. Does not include RAW/engineering fields.',
    properties: [
        new OA\Property(
            property: 'power_fractions',
            description: 'Fractions of the generator\'s total power budget reserved for idle, pulse, and jamming phases.',
            properties: [
                new OA\Property(property: 'base', type: 'double', example: 0.2, nullable: true),
                new OA\Property(property: 'pulse', type: 'double', example: 0.8, nullable: true),
                new OA\Property(property: 'jammer', type: 'double', example: 0.4, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'jamming',
            description: 'Continuous interdiction field used to yank ships out of quantum travel.',
            properties: [
                new OA\Property(property: 'range', description: 'Effective jamming radius in meters.', type: 'double', example: 12000, nullable: true),
                new OA\Property(property: 'max_power_draw', description: 'Peak power draw while the jammer is active.', type: 'double', example: 200, nullable: true),
                new OA\Property(property: 'green_zone_check_range', description: 'Safety buffer around restricted areas.', type: 'double', example: 4000, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'pulse',
            description: 'Quantum interdiction pulse (QIP) burst stats.',
            properties: [
                new OA\Property(property: 'charge_time', description: 'Seconds to charge the pulse.', type: 'double', example: 90, nullable: true),
                new OA\Property(property: 'discharge_time', description: 'Duration of the active pulse.', type: 'double', example: 30, nullable: true),
                new OA\Property(property: 'cooldown_time', description: 'Cooldown before the next charge cycle.', type: 'double', example: 1, nullable: true),
                new OA\Property(property: 'radius', description: 'Pulse radius in meters.', type: 'double', example: 20000, nullable: true),
                new OA\Property(property: 'decrease_charge_rate_time', description: 'Seconds to ramp down charging rate.', type: 'double', example: 1.5, nullable: true),
                new OA\Property(property: 'increase_charge_rate_time', description: 'Seconds to ramp up charging rate.', type: 'double', example: 3.5, nullable: true),
                new OA\Property(property: 'activation_phase_duration', description: 'Activation animation/FX phase length.', type: 'double', example: 3, nullable: true),
                new OA\Property(property: 'disperse_charge_time', description: 'Time to disperse the pulse if cancelled.', type: 'double', example: 5, nullable: true),
                new OA\Property(property: 'max_power_draw', description: 'Maximum power draw during pulse operations.', type: 'double', example: 1100, nullable: true),
                new OA\Property(property: 'stop_charging_power_fraction', description: 'Power fraction at which charging halts.', type: 'double', example: 0.2, nullable: true),
                new OA\Property(property: 'max_charge_rate_power_fraction', description: 'Peak charge-rate power fraction.', type: 'double', example: 0.85, nullable: true),
                new OA\Property(property: 'active_power_fraction', description: 'Power fraction while pulse is active.', type: 'double', example: 0.95, nullable: true),
                new OA\Property(property: 'tethering_power_fraction', description: 'Power fraction while maintaining tether.', type: 'double', example: 0.8, nullable: true),
                new OA\Property(property: 'green_zone_check_range', description: 'Safety buffer used for pulse firing checks.', type: 'double', example: 30000, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'interdiction_range',
            description: 'Legacy top-level interdiction range mirror (meters). Prefer pulse.radius.',
            type: 'double',
            example: 20000,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'jammer_range',
            description: 'Legacy jamming range mirror (meters). Prefer jamming.range.',
            type: 'double',
            example: 12000,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'charge_duration',
            description: 'Legacy pulse charge duration. Prefer pulse.charge_time.',
            type: 'double',
            example: 90,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'discharge_duration',
            description: 'Legacy pulse discharge duration. Prefer pulse.discharge_time.',
            type: 'double',
            example: 30,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'cooldown_duration',
            description: 'Legacy pulse cooldown duration. Prefer pulse.cooldown_time.',
            type: 'double',
            example: 1,
            nullable: true,
            deprecated: true
        ),
    ],
    type: 'object'
)]
class QuantumInterdictionGeneratorResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $quantumInterdiction = Arr::get($data, 'stdItem.QuantumInterdictionGenerator', []);

        $jammer = Arr::get($quantumInterdiction, 'Jammer', []);
        $pulse = Arr::get($quantumInterdiction, 'Pulse', []);

        return [
            'power_fractions' => [
                'base' => Arr::get($quantumInterdiction, 'BasePowerDrawFraction'),
                'pulse' => Arr::get($quantumInterdiction, 'PulsePowerFraction'),
                'jammer' => Arr::get($quantumInterdiction, 'JammerPowerFraction'),
            ],
            'jamming' => [
                'range' => Arr::get($quantumInterdiction, 'JammingRange'),
                'max_power_draw' => Arr::get($jammer, 'MaxPowerDraw'),
                'green_zone_check_range' => Arr::get($jammer, 'GreenZoneCheckRange'),
            ],
            'pulse' => [
                'charge_time' => Arr::get($pulse, 'ChargeTimeSecs'),
                'discharge_time' => Arr::get($pulse, 'DischargeTimeSecs'),
                'cooldown_time' => Arr::get($pulse, 'CooldownTimeSecs'),
                'radius' => Arr::get($pulse, 'RadiusMeters'),
                'decrease_charge_rate_time' => Arr::get($pulse, 'DecreaseChargeRateTimeSeconds'),
                'increase_charge_rate_time' => Arr::get($pulse, 'IncreaseChargeRateTimeSeconds'),
                'activation_phase_duration' => Arr::get($pulse, 'ActivationPhaseDuration_seconds'),
                'disperse_charge_time' => Arr::get($pulse, 'DisperseChargeTimeSeconds'),
                'max_power_draw' => Arr::get($pulse, 'MaxPowerDraw'),
                'stop_charging_power_fraction' => Arr::get($pulse, 'StopChargingPowerDrawFraction'),
                'max_charge_rate_power_fraction' => Arr::get($pulse, 'MaxChargeRatePowerDrawFraction'),
                'active_power_fraction' => Arr::get($pulse, 'ActivePowerDrawFraction'),
                'tethering_power_fraction' => Arr::get($pulse, 'TetheringPowerDrawFraction'),
                'green_zone_check_range' => Arr::get($pulse, 'GreenZoneCheckRange'),
            ],

            'interdiction_range' => Arr::get($quantumInterdiction, 'InterdictionRange'),
            'jammer_range' => Arr::get($quantumInterdiction, 'JammingRange'),

            'charge_duration' => Arr::get($pulse, 'ChargeTimeSecs'),
            'activation_duration' => Arr::get($pulse, 'ActivationPhaseDurationSeconds'),
            'discharge_duration' => Arr::get($pulse, 'DischargeTimeSecs'),
            'cooldown_duration' => Arr::get($pulse, 'CooldownTimeSecs'),
            'disperse_charge_duration' => Arr::get($pulse, 'DisperseChargeTimeSeconds'),
        ];
    }
}
