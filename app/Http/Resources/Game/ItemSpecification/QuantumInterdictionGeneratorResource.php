<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'quantum_interdiction_generator_power_fractions',
    title: 'Quantum Interdiction Generator Power Fractions',
    description: 'Fractions of the generator total power budget reserved for base/idle, pulse, and jammer phases.',
    properties: [
        new OA\Property(property: 'base', description: 'Power fraction allocated to base idle operation.', type: 'double', example: 0.2, nullable: true, x: ['tabulator-formatter' => 'pct']),
        new OA\Property(property: 'pulse', description: 'Power fraction allocated to pulse operations.', type: 'double', example: 0.8, nullable: true, x: ['tabulator-formatter' => 'pct']),
        new OA\Property(property: 'jammer', description: 'Power fraction allocated to jammer operations.', type: 'double', example: 0.4, nullable: true, x: ['tabulator-formatter' => 'pct']),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'quantum_interdiction_generator_jamming',
    title: 'Quantum Interdiction Generator Jamming',
    description: 'Continuous interdiction field used to yank ships out of quantum travel.',
    properties: [
        new OA\Property(property: 'range', description: 'Effective jamming radius in meters.', type: 'double', example: 12000, nullable: true, x: ['suffix' => ' m']),
        new OA\Property(property: 'max_power_draw', description: 'Peak power draw while the jammer is active.', type: 'double', example: 200, nullable: true),
        new OA\Property(property: 'green_zone_check_range', description: 'Safety buffer around restricted areas.', type: 'double', example: 4000, nullable: true, x: ['suffix' => ' m']),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'quantum_interdiction_generator_pulse',
    title: 'Quantum Interdiction Generator Pulse',
    description: 'Quantum interdiction pulse (QIP) burst stats.',
    properties: [
        new OA\Property(property: 'charge_time', description: 'Seconds to charge the pulse.', type: 'double', example: 90, nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'discharge_time', description: 'Duration of the active pulse.', type: 'double', example: 30, nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'cooldown_time', description: 'Cooldown before the next charge cycle.', type: 'double', example: 1, nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'radius', description: 'Pulse radius in meters.', type: 'double', example: 20000, nullable: true, x: ['suffix' => ' m']),

        new OA\Property(property: 'decrease_charge_rate_time', description: 'Seconds to ramp down charging rate.', type: 'double', example: 1.5, nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'increase_charge_rate_time', description: 'Seconds to ramp up charging rate.', type: 'double', example: 3.5, nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'activation_phase_duration', description: 'Activation animation/FX phase length.', type: 'double', example: 3, nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'disperse_charge_time', description: 'Time to disperse the pulse if cancelled.', type: 'double', example: 5, nullable: true, x: ['suffix' => ' s']),

        new OA\Property(property: 'max_power_draw', description: 'Maximum power draw during pulse operations.', type: 'double', example: 1100, nullable: true),

        new OA\Property(property: 'stop_charging_power_fraction', description: 'Power fraction at which charging halts.', type: 'double', example: 0.2, nullable: true, x: ['tabulator-formatter' => 'pct']),
        new OA\Property(property: 'max_charge_rate_power_fraction', description: 'Peak charge-rate power fraction.', type: 'double', example: 0.85, nullable: true, x: ['tabulator-formatter' => 'pct']),
        new OA\Property(property: 'active_power_fraction', description: 'Power fraction while pulse is active.', type: 'double', example: 0.95, nullable: true, x: ['tabulator-formatter' => 'pct']),
        new OA\Property(property: 'tethering_power_fraction', description: 'Power fraction while maintaining tether.', type: 'double', example: 0.8, nullable: true, x: ['tabulator-formatter' => 'pct']),

        new OA\Property(property: 'green_zone_check_range', description: 'Safety buffer used for pulse firing checks.', type: 'double', example: 30000, nullable: true, x: ['suffix' => ' m']),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'quantum_interdiction_generator',
    title: 'Quantum Interdiction Generator',
    description: 'Interdiction and jamming stats including power budget, jamming field, and pulse burst data.',
    properties: [
        new OA\Property(
            property: 'power_fractions',
            ref: '#/components/schemas/quantum_interdiction_generator_power_fractions',
            description: 'Fractions of the generator total power budget reserved for base/idle, pulse, and jammer phases.',
            nullable: true
        ),
        new OA\Property(
            property: 'jamming',
            ref: '#/components/schemas/quantum_interdiction_generator_jamming',
            description: 'Continuous interdiction field used to yank ships out of quantum travel.',
            nullable: true
        ),
        new OA\Property(
            property: 'pulse',
            ref: '#/components/schemas/quantum_interdiction_generator_pulse',
            description: 'Quantum interdiction pulse (QIP) burst stats.',
            nullable: true
        ),

        new OA\Property(
            property: 'interdiction_range',
            description: 'Deprecated. Use `pulse.radius`.',
            type: 'double',
            example: 20000,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'jammer_range',
            description: 'Deprecated. Use `jamming.range`.',
            type: 'double',
            example: 12000,
            nullable: true,
            deprecated: true
        ),

        new OA\Property(
            property: 'charge_duration',
            description: 'Deprecated. Use `pulse.charge_time`.',
            type: 'double',
            example: 90,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'activation_duration',
            description: 'Deprecated. Use `pulse.activation_phase_duration`.',
            type: 'double',
            example: 3,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'discharge_duration',
            description: 'Deprecated. Use `pulse.discharge_time`.',
            type: 'double',
            example: 30,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'cooldown_duration',
            description: 'Deprecated. Use `pulse.cooldown_time`.',
            type: 'double',
            example: 1,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'disperse_charge_duration',
            description: 'Deprecated. Use `pulse.disperse_charge_time`.',
            type: 'double',
            example: 5,
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
