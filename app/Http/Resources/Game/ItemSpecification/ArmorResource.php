<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
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
        $armor = $stdItem['Armor'] ?? [];
        $signalMultipliers = $armor['SignalMultipliers'] ?? [];
        $damageMultipliers = $armor['DamageMultipliers'] ?? [];
        $penetration = $armor['PenetrationResistance'] ?? [];
        $deflection = $armor['Deflection'] ?? [];
        $durability = $stdItem['Durability'] ?? [];
        $resistance = $durability['Resistance'] ?? [];

        // Compute each multiplier once: the _change fields reuse the same value,
        // halving the number of nested-array reads for signal/damage/resistance.
        $sigCs = $signalMultipliers['CrossSection'] ?? null;
        $sigIr = $signalMultipliers['Infrared'] ?? null;
        $sigEm = $signalMultipliers['Electromagnetic'] ?? null;

        $dmgPhys = $damageMultipliers['Physical'] ?? null;
        $dmgEnergy = $damageMultipliers['Energy'] ?? null;
        $dmgDist = $damageMultipliers['Distortion'] ?? null;
        $dmgTherm = $damageMultipliers['Thermal'] ?? null;
        $dmgBio = $damageMultipliers['Biochemical'] ?? null;
        $dmgStun = $damageMultipliers['Stun'] ?? null;

        $resPhys = $resistance['Physical']['Multiplier'] ?? null;
        $resEnergy = $resistance['Energy']['Multiplier'] ?? null;
        $resDist = $resistance['Distortion']['Multiplier'] ?? null;
        $resTherm = $resistance['Thermal']['Multiplier'] ?? null;
        $resBio = $resistance['Biochemical']['Multiplier'] ?? null;
        $resStun = $resistance['Stun']['Multiplier'] ?? null;

        return [
            'uuid' => $this->resource->item?->uuid,
            'health' => $durability['Health'] ?? null,

            'signal_infrared' => $sigIr,
            'signal_electromagnetic' => $sigEm,
            'signal_cross_section' => $sigCs,
            'damage_physical' => $dmgPhys,
            'damage_energy' => $dmgEnergy,
            'damage_distortion' => $dmgDist,
            'damage_thermal' => $dmgTherm,
            'damage_biochemical' => $dmgBio,
            'damage_stun' => $dmgStun,

            'signal_multiplier' => [
                'cross_section' => $sigCs,
                'cross_section_change' => round($sigCs - 1, 2),

                'infrared' => $sigIr,
                'infrared_change' => round($sigIr - 1, 2),

                'electromagnetic' => $sigEm,
                'electromagnetic_change' => round($sigEm - 1, 2),
            ],
            'damage_multiplier' => [
                'physical' => $dmgPhys,
                'physical_change' => round($dmgPhys - 1, 2),

                'energy' => $dmgEnergy,
                'energy_change' => round($dmgEnergy - 1, 2),

                'distortion' => $dmgDist,
                'distortion_change' => round($dmgDist - 1, 2),

                'thermal' => $dmgTherm,
                'thermal_change' => round($dmgTherm - 1, 2),

                'biochemical' => $dmgBio,
                'biochemical_change' => round($dmgBio - 1, 2),

                'stun' => $dmgStun,
                'stun_change' => round($dmgStun - 1, 2),
            ],
            'resistance_multiplier' => [
                'physical' => $resPhys,
                'physical_change' => round($resPhys - 1, 2),

                'energy' => $resEnergy,
                'energy_change' => round($resEnergy - 1, 2),
                'distortion' => $resDist,
                'distortion_change' => round($resDist - 1, 2),
                'thermal' => $resTherm,
                'thermal_change' => round($resTherm - 1, 2),
                'biochemical' => $resBio,
                'biochemical_change' => round($resBio - 1, 2),
                'stun' => $resStun,
                'stun_change' => round($resStun - 1, 2),
            ],
            'penetration_resistance' => [
                'base' => $penetration['Base'] ?? null,
                'physical' => $penetration['Physical'] ?? null,
                'energy' => $penetration['Energy'] ?? null,
                'distortion' => $penetration['Distortion'] ?? null,
                'thermal' => $penetration['Thermal'] ?? null,
                'biochemical' => $penetration['Biochemical'] ?? null,
                'stun' => $penetration['Stun'] ?? null,
            ],
            'deflection' => [
                'physical' => $deflection['Physical'] ?? null,
                'energy' => $deflection['Energy'] ?? null,
                'distortion' => $deflection['Distortion'] ?? null,
                'thermal' => $deflection['Thermal'] ?? null,
                'biochemical' => $deflection['Biochemical'] ?? null,
                'stun' => $deflection['Stun'] ?? null,
            ],
        ];
    }
}
