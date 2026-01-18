<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'suit_armor_type_resistance',
    title: 'Suit Armor Type Resistance',
    description: 'Per-damage-type resistance entry as returned by the resource.',
    properties: [
        new OA\Property(property: 'type', description: 'Damage type identifier (lowercase).', type: 'string', example: 'physical', nullable: true),
        new OA\Property(property: 'multiplier', type: 'double', example: 0.7, nullable: true),
        new OA\Property(property: 'threshold', type: 'double', example: 0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'suit_armor_damage_resistance',
    title: 'Suit Armor Damage Resistance',
    description: 'Deprecated: Use damage_resistance_map. Damage resistance values for the armor piece.',
    properties: [
        new OA\Property(property: 'impact', type: 'double', example: 0.6925, nullable: true),

        new OA\Property(property: 'physical', ref: '#/components/schemas/suit_armor_type_resistance', nullable: true),
        new OA\Property(property: 'energy', ref: '#/components/schemas/suit_armor_type_resistance', nullable: true),
        new OA\Property(property: 'distortion', ref: '#/components/schemas/suit_armor_type_resistance', nullable: true),
        new OA\Property(property: 'thermal', ref: '#/components/schemas/suit_armor_type_resistance', nullable: true),
        new OA\Property(property: 'biochemical', ref: '#/components/schemas/suit_armor_type_resistance', nullable: true),
        new OA\Property(property: 'stun', ref: '#/components/schemas/suit_armor_type_resistance', nullable: true),
    ],
    type: 'object',
    deprecated: true
)]
#[OA\Schema(
    schema: 'suit_armor_damage_resistance_map',
    title: 'Suit Armor Damage Resistance Map',
    description: 'Flattened resistance values and deltas (multiplier change vs 1.0). Returned for convenience.',
    properties: [
        new OA\Property(property: 'impact', type: 'double', example: 0.6925, nullable: true),
        new OA\Property(property: 'impact_change', type: 'double', example: -0.3075, nullable: true),

        new OA\Property(property: 'physical', type: 'double', example: 0.7, nullable: true),
        new OA\Property(property: 'physical_change', type: 'double', example: -0.3, nullable: true),

        new OA\Property(property: 'energy', type: 'double', example: 0.7, nullable: true),
        new OA\Property(property: 'energy_change', type: 'double', example: -0.3, nullable: true),

        new OA\Property(property: 'distortion', type: 'double', example: 0.7, nullable: true),
        new OA\Property(property: 'distortion_change', type: 'double', example: -0.3, nullable: true),

        new OA\Property(property: 'thermal', type: 'double', example: 0.7, nullable: true),
        new OA\Property(property: 'thermal_change', type: 'double', example: -0.3, nullable: true),

        new OA\Property(property: 'biochemical', type: 'double', example: 0.7, nullable: true),
        new OA\Property(property: 'biochemical_change', type: 'double', example: -0.3, nullable: true),

        new OA\Property(property: 'stun', type: 'double', example: 0.55, nullable: true),
        new OA\Property(property: 'stun_change', type: 'double', example: -0.45, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'suit_armor_signature',
    title: 'Suit Armor Signature',
    description: 'Map of signature emission types (snake_case) to emission values.',
    type: 'object',
    additionalProperties: new OA\AdditionalProperties(type: 'double'),
)]
#[OA\Schema(
    schema: 'suit_armor',
    title: 'Suit Armor',
    description: 'Protective characteristics of FPS armor pieces (helmets, cores, arms, legs). Generated from SuitArmor data in Item.stdItem.',
    properties: [
        new OA\Property(
            property: 'slot',
            description: 'Armor slot derived from the item classification (e.g. Arms, Core, Legs, Helmet).',
            type: 'string',
            example: 'Arms',
            nullable: true
        ),
        new OA\Property(
            property: 'armor_type',
            description: 'Deprecated legacy field. Use `slot`.',
            type: 'string',
            example: 'Arms',
            nullable: true,
            deprecated: true
        ),

        new OA\Property(
            property: 'damage_resistance',
            ref: '#/components/schemas/suit_armor_damage_resistance',
            description: 'Structured resistance values.',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'damage_resistance_map',
            ref: '#/components/schemas/suit_armor_damage_resistance_map',
            description: 'Flattened resistance values and multiplier deltas.',
            nullable: true
        ),

        new OA\Property(
            property: 'protected_body_parts',
            description: 'UUIDs of body parts covered by this armor.',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['b6d0be4f-bbdd-4a47-a168-8c9c9293c63a', 'e9c7c16d-d408-41c1-8d13-eec69cd2a013']
        ),

        new OA\Property(
            property: 'signature',
            ref: '#/components/schemas/suit_armor_signature',
            description: 'Signature emissions produced by the armor (map form).',
            example: [
                'electromagnetic' => 5,
                'infrared' => 2,
            ],
            nullable: true
        ),

        new OA\Property(
            property: 'temp_resistance_min',
            description: 'Deprecated: Use temperature_resistance from root.',
            type: 'double',
            example: 2,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'temp_resistance_max',
            description: 'Deprecated: Use temperature_resistance from root.',
            type: 'double',
            example: 10,
            nullable: true,
            deprecated: true
        ),

        new OA\Property(
            property: 'radiation_resistance',
            ref: '#/components/schemas/radiation_resistance',
            description: 'Radiation protection values from RadiationResistance.',
            example: [
                'maximum_radiation_capacity' => 26400,
                'radiation_dissipation_rate' => 145.8,
            ],
            nullable: true
        ),
    ],
    type: 'object'
)]
class SuitArmorResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $stdItem = $this->extractStdItem($data);
        $armor = Arr::get($stdItem, 'SuitArmor', []);

        $slot = $this->deriveSlot();

        return [
            'slot' => $slot,
            'armor_type' => $slot,
            'damage_resistance' => [
                'impact' => Arr::get($armor, 'DamageResistance.Impact'),
                'physical' => $this->mapTypeResistance($armor, 'Physical'),
                'energy' => $this->mapTypeResistance($armor, 'Energy'),
                'distortion' => $this->mapTypeResistance($armor, 'Distortion'),
                'thermal' => $this->mapTypeResistance($armor, 'Thermal'),
                'biochemical' => $this->mapTypeResistance($armor, 'Biochemical'),
                'stun' => $this->mapTypeResistance($armor, 'Stun'),
            ],
            'damage_resistance_map' => [
                'impact' => Arr::get($armor, 'DamageResistance.Impact'),
                'impact_change' => Arr::get($armor, 'DamageResistance.Impact', 1) - 1,
                'physical' => Arr::get($this->mapTypeResistance($armor, 'Physical'), 'multiplier'),
                'physical_change' => Arr::get($this->mapTypeResistance($armor, 'Physical'), 'multiplier', 1) - 1,
                'energy' => Arr::get($this->mapTypeResistance($armor, 'Energy'), 'multiplier'),
                'energy_change' => Arr::get($this->mapTypeResistance($armor, 'Energy'), 'multiplier', 1) - 1,
                'distortion' => Arr::get($this->mapTypeResistance($armor, 'Distortion'), 'multiplier'),
                'distortion_change' => Arr::get($this->mapTypeResistance($armor, 'Distortion'), 'multiplier', 1) - 1,
                'thermal' => Arr::get($this->mapTypeResistance($armor, 'Thermal'), 'multiplier'),
                'thermal_change' => Arr::get($this->mapTypeResistance($armor, 'Thermal'), 'multiplier', 1) - 1,
                'biochemical' => Arr::get($this->mapTypeResistance($armor, 'Biochemical'), 'multiplier'),
                'biochemical_change' => Arr::get($this->mapTypeResistance($armor, 'Biochemical'), 'multiplier', 1) - 1,
                'stun' => Arr::get($this->mapTypeResistance($armor, 'Stun'), 'multiplier'),
                'stun_change' => Arr::get($this->mapTypeResistance($armor, 'Stun'), 'multiplier', 1) - 1,
            ],
            'protected_body_parts' => Arr::get($armor, 'ProtectedBodyParts', []),
            'signature' => collect(Arr::get($armor, 'Signature', []))
                ->mapWithKeys(static fn ($value, $key) => [
                    Str::snake($key) => $value,
                ])
                ->toArray(),
            'temp_resistance_min' => Arr::get($stdItem, 'data.stdItem.TemperatureResistance.Minimum'),
            'temp_resistance_max' => Arr::get($stdItem, 'data.stdItem.TemperatureResistance.Maximum'),
            'radiation_resistance' => Arr::has($stdItem, 'RadiationResistance')
                ? (new RadiationResistanceResource(Arr::get($stdItem, 'RadiationResistance')))->toArray($request)
                : null,
        ];
    }

    private function mapTypeResistance(array $armor, string $key): ?array
    {
        if (! Arr::has($armor, "DamageResistance.{$key}")) {
            return null;
        }

        return [
            'type' => strtolower($key),
            'multiplier' => Arr::get($armor, "DamageResistance.{$key}.Multiplier"),
            'threshold' => Arr::get($armor, "DamageResistance.{$key}.Threshold"),
        ];
    }

    private function deriveSlot(): ?string
    {
        $classification = $this->resource->classification ?? Arr::get($this->resource, 'classification');

        if (! is_string($classification)) {
            return null;
        }

        $parts = explode('.', $classification);

        return $parts !== [] ? Arr::last($parts) : null;
    }
}
