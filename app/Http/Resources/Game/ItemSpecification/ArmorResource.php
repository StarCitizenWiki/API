<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_armor',
    title: 'Vehicle Armor',
    description: 'Armor characteristics for ship and vehicle hull plating. Signal multipliers affect detectability (values < 1.0 reduce signature for stealth, > 1.0 increase signature). Damage multipliers determine resistance to damage types (lower values = more resistant).',
    properties: [
        new OA\Property(
            property: 'uuid',
            description: 'Armor item UUID.',
            type: 'string',
            nullable: true
        ),
        new OA\Property(
            property: 'health',
            description: 'Armor health points from Durability system.',
            type: 'double',
            nullable: true
        ),
        new OA\Property(
            property: 'signal_infrared',
            description: 'Deprecated: Use signal_multiplier.infrared instead. Infrared signature multiplier. Lower values make the ship harder to detect via heat signature.',
            type: 'double',
            example: 1.0,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'signal_electromagnetic',
            description: 'Deprecated: Use signal_multiplier.electromagnetic instead. Electromagnetic signature multiplier. Lower values provide better EM stealth.',
            type: 'double',
            example: 1.0,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'signal_cross_section',
            description: 'Deprecated: Use signal_multiplier.cross_section instead. Radar cross-section multiplier. Affects radar detectability.',
            type: 'double',
            example: 1.0,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'damage_physical',
            description: 'Deprecated: Use damage_multiplier.physical instead. Physical damage multiplier. Typically 0.62, providing 38% damage reduction.',
            type: 'double',
            example: 0.62,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'damage_energy',
            description: 'Deprecated: Use damage_multiplier.energy instead. Energy weapon damage multiplier. Values around 1.0 are neutral.',
            type: 'double',
            example: 1.0,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'damage_distortion',
            description: 'Deprecated: Use damage_multiplier.distortion instead. Distortion damage multiplier. Typically around 1.0.',
            type: 'double',
            example: 1.0,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'damage_thermal',
            description: 'Deprecated: Use damage_multiplier.thermal instead. Thermal damage multiplier. Typically 1.0 (neutral, no resistance).',
            type: 'double',
            example: 1.0,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'damage_biochemical',
            description: 'Deprecated: Use damage_multiplier.biochemical instead. Biochemical damage multiplier. Typically 1.0 (neutral, no resistance).',
            type: 'double',
            example: 1.0,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'damage_stun',
            description: 'Deprecated: Use damage_multiplier.stun instead. Stun damage multiplier. Typically 0 (complete immunity to stun).',
            type: 'double',
            example: 0,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'signal_multiplier',
            description: 'Grouped signal multipliers affecting detectability. Replacement for individual signal_* fields.',
            properties: [
                new OA\Property(property: 'cross_section', description: 'Radar cross-section multiplier.', type: 'double', nullable: true),
                new OA\Property(property: 'cross_section_change', description: 'Cross-section change from baseline (multiplier - 1). Negative values indicate reduction.', type: 'double', nullable: true),
                new OA\Property(property: 'infrared', description: 'Infrared signature multiplier. Lower values reduce heat detectability.', type: 'double', nullable: true),
                new OA\Property(property: 'infrared_change', description: 'Infrared change from baseline (multiplier - 1).', type: 'double', nullable: true),
                new OA\Property(property: 'electromagnetic', description: 'Electromagnetic signature multiplier. Lower values improve EM stealth.', type: 'double', nullable: true),
                new OA\Property(property: 'electromagnetic_change', description: 'EM change from baseline (multiplier - 1).', type: 'double', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'damage_multiplier',
            description: 'Grouped damage multipliers determining armor resistance. Lower values = more resistant. Replacement for individual damage_* fields.',
            properties: [
                new OA\Property(property: 'physical', description: 'Physical damage multiplier. Typical value 0.62 (38% reduction).', type: 'double', nullable: true),
                new OA\Property(property: 'physical_change', description: 'Physical resistance change from neutral (multiplier - 1). Negative = more resistant.', type: 'double', nullable: true),
                new OA\Property(property: 'energy', description: 'Energy damage multiplier.', type: 'double', nullable: true),
                new OA\Property(property: 'energy_change', description: 'Energy resistance change from neutral.', type: 'double', nullable: true),
                new OA\Property(property: 'distortion', description: 'Distortion damage multiplier.', type: 'double', nullable: true),
                new OA\Property(property: 'distortion_change', description: 'Distortion resistance change from neutral.', type: 'double', nullable: true),
                new OA\Property(property: 'thermal', description: 'Thermal damage multiplier.', type: 'double', nullable: true),
                new OA\Property(property: 'thermal_change', description: 'Thermal resistance change from neutral.', type: 'double', nullable: true),
                new OA\Property(property: 'biochemical', description: 'Biochemical damage multiplier.', type: 'double', nullable: true),
                new OA\Property(property: 'biochemical_change', description: 'Biochemical resistance change from neutral.', type: 'double', nullable: true),
                new OA\Property(property: 'stun', description: 'Stun damage multiplier. 0 = complete immunity.', type: 'double', nullable: true),
                new OA\Property(property: 'stun_change', description: 'Stun resistance change from neutral.', type: 'double', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'resistance_multiplier',
            description: 'Durability-based resistance multipliers from stdItem.Durability.Resistance system.',
            properties: [
                new OA\Property(property: 'physical', description: 'Physical resistance multiplier from Durability.', type: 'double', nullable: true),
                new OA\Property(property: 'physical_change', description: 'Physical resistance change from neutral (multiplier - 1).', type: 'double', nullable: true),
                new OA\Property(property: 'energy', description: 'Energy resistance multiplier from Durability.', type: 'double', nullable: true),
                new OA\Property(property: 'energy_change', description: 'Energy resistance change from neutral (multiplier - 1).', type: 'double', nullable: true),
                new OA\Property(property: 'distortion', description: 'Distortion resistance multiplier from Durability.', type: 'double', nullable: true),
                new OA\Property(property: 'distortion_change', description: 'Distortion resistance change from neutral (multiplier - 1).', type: 'double', nullable: true),
                new OA\Property(property: 'thermal', description: 'Thermal resistance multiplier from Durability.', type: 'double', nullable: true),
                new OA\Property(property: 'thermal_change', description: 'Thermal resistance change from neutral (multiplier - 1).', type: 'double', nullable: true),
                new OA\Property(property: 'biochemical', description: 'Biochemical resistance multiplier from Durability.', type: 'double', nullable: true),
                new OA\Property(property: 'biochemical_change', description: 'Biochemical resistance change from neutral (multiplier - 1).', type: 'double', nullable: true),
                new OA\Property(property: 'stun', description: 'Stun resistance multiplier from Durability.', type: 'double', nullable: true),
                new OA\Property(property: 'stun_change', description: 'Stun resistance change from neutral (multiplier - 1).', type: 'double', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'deflection',
            description: 'Deflection values determining how well armor deflects incoming rounds by type.',
            properties: [
                new OA\Property(property: 'physical', description: 'Physical deflection value.', type: 'double', nullable: true),
                new OA\Property(property: 'energy', description: 'Energy deflection value.', type: 'double', nullable: true),
                new OA\Property(property: 'distortion', description: 'Distortion deflection value.', type: 'double', nullable: true),
                new OA\Property(property: 'thermal', description: 'Thermal deflection value.', type: 'double', nullable: true),
                new OA\Property(property: 'biochemical', description: 'Biochemical deflection value.', type: 'double', nullable: true),
                new OA\Property(property: 'stun', description: 'Stun deflection value.', type: 'double', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'penetration_resistance',
            description: 'Penetration resistance values determining how well armor resists armor-piercing rounds.',
            properties: [
                new OA\Property(property: 'base', description: 'Base penetration resistance value.', type: 'double', nullable: true),
                new OA\Property(property: 'physical', description: 'Physical penetration resistance.', type: 'double', nullable: true),
                new OA\Property(property: 'energy', description: 'Energy penetration resistance.', type: 'double', nullable: true),
                new OA\Property(property: 'distortion', description: 'Distortion penetration resistance.', type: 'double', nullable: true),
                new OA\Property(property: 'thermal', description: 'Thermal penetration resistance.', type: 'double', nullable: true),
                new OA\Property(property: 'biochemical', description: 'Biochemical penetration resistance.', type: 'double', nullable: true),
                new OA\Property(property: 'stun', description: 'Stun penetration resistance.', type: 'double', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
    ],
    type: 'object'
)]
class ArmorResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $stdItem = $this->extractStdItem($data);
        $armor = Arr::get($stdItem, 'Armor', []);
        $signalMultipliers = Arr::get($armor, 'SignalMultipliers', []);
        $damageMultipliers = Arr::get($armor, 'DamageMultipliers', []);

        return [
            'uuid' => $this->resource->item?->uuid,
            'health' => Arr::get($stdItem, 'Durability.Health'),

            'signal_infrared' => Arr::get($signalMultipliers, 'Infrared'),
            'signal_electromagnetic' => Arr::get($signalMultipliers, 'Electromagnetic'),
            'signal_cross_section' => Arr::get($signalMultipliers, 'CrossSection'),
            'damage_physical' => Arr::get($damageMultipliers, 'Physical'),
            'damage_energy' => Arr::get($damageMultipliers, 'Energy'),
            'damage_distortion' => Arr::get($damageMultipliers, 'Distortion'),
            'damage_thermal' => Arr::get($damageMultipliers, 'Thermal'),
            'damage_biochemical' => Arr::get($damageMultipliers, 'Biochemical'),
            'damage_stun' => Arr::get($damageMultipliers, 'Stun'),

            'signal_multiplier' => [
                'cross_section' => Arr::get($armor, 'SignalMultipliers.CrossSection'),
                'cross_section_change' => round(Arr::get($armor, 'SignalMultipliers.CrossSection') - 1, 2),

                'infrared' => Arr::get($armor, 'SignalMultipliers.Infrared'),
                'infrared_change' => round(Arr::get($armor, 'SignalMultipliers.Infrared') - 1, 2),

                'electromagnetic' => Arr::get($armor, 'SignalMultipliers.Electromagnetic'),
                'electromagnetic_change' => round(Arr::get($armor, 'SignalMultipliers.Electromagnetic') - 1, 2),
            ],
            'damage_multiplier' => [
                'physical' => Arr::get($armor, 'DamageMultipliers.Physical'),
                'physical_change' => round(Arr::get($armor, 'DamageMultipliers.Physical') - 1, 2),

                'energy' => Arr::get($armor, 'DamageMultipliers.Energy'),
                'energy_change' => round(Arr::get($armor, 'DamageMultipliers.Energy') - 1, 2),

                'distortion' => Arr::get($armor, 'DamageMultipliers.Distortion'),
                'distortion_change' => round(Arr::get($armor, 'DamageMultipliers.Distortion') - 1, 2),

                'thermal' => Arr::get($armor, 'DamageMultipliers.Thermal'),
                'thermal_change' => round(Arr::get($armor, 'DamageMultipliers.Thermal') - 1, 2),

                'biochemical' => Arr::get($armor, 'DamageMultipliers.Biochemical'),
                'biochemical_change' => round(Arr::get($armor, 'DamageMultipliers.Biochemical') - 1, 2),

                'stun' => Arr::get($armor, 'DamageMultipliers.Stun'),
                'stun_change' => round(Arr::get($armor, 'DamageMultipliers.Stun') - 1, 2),
            ],
            'resistance_multiplier' => [
                'physical' => Arr::get($stdItem, 'Durability.Resistance.Physical.Multiplier'),
                'physical_change' => round(Arr::get($stdItem, 'Durability.Resistance.Physical.Multiplier') - 1, 2),

                'energy' => Arr::get($stdItem, 'Durability.Resistance.Energy.Multiplier'),
                'energy_change' => round(Arr::get($stdItem, 'Durability.Resistance.Energy.Multiplier') - 1, 2),
                'distortion' => Arr::get($stdItem, 'Durability.Resistance.Distortion.Multiplier'),
                'distortion_change' => round(Arr::get($stdItem, 'Durability.Resistance.Distortion.Multiplier') - 1, 2),
                'thermal' => Arr::get($stdItem, 'Durability.Resistance.Thermal.Multiplier'),
                'thermal_change' => round(Arr::get($stdItem, 'Durability.Resistance.Thermal.Multiplier') - 1, 2),
                'biochemical' => Arr::get($stdItem, 'Durability.Resistance.Biochemical.Multiplier'),
                'biochemical_change' => round(Arr::get($stdItem, 'Durability.Resistance.Biochemical.Multiplier') - 1, 2),
                'stun' => Arr::get($stdItem, 'Durability.Resistance.Stun.Multiplier'),
                'stun_change' => round(Arr::get($stdItem, 'Durability.Resistance.Stun.Multiplier') - 1, 2),
            ],
            'penetration_resistance' => [
                'base' => Arr::get($armor, 'PenetrationResistance.Base'),
                'physical' => Arr::get($armor, 'PenetrationResistance.Physical'),
                'energy' => Arr::get($armor, 'PenetrationResistance.Energy'),
                'distortion' => Arr::get($armor, 'PenetrationResistance.Distortion'),
                'thermal' => Arr::get($armor, 'PenetrationResistance.Thermal'),
                'biochemical' => Arr::get($armor, 'PenetrationResistance.Biochemical'),
                'stun' => Arr::get($armor, 'PenetrationResistance.Stun'),
            ],
            'deflection' => [
                'physical' => Arr::get($armor, 'Deflection.Physical'),
                'energy' => Arr::get($armor, 'Deflection.Energy'),
                'distortion' => Arr::get($armor, 'Deflection.Distortion'),
                'thermal' => Arr::get($armor, 'Deflection.Thermal'),
                'biochemical' => Arr::get($armor, 'Deflection.Biochemical'),
                'stun' => Arr::get($armor, 'Deflection.Stun'),
            ],
        ];
    }
}
