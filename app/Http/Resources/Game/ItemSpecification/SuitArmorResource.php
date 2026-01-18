<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'suit_armor',
    title: 'Suit Armor Armor',
    description: 'Protective characteristics of FPS armor pieces (helmets, cores, arms, legs). Generated from SuitArmor data in Item.stdItem.',
    properties: [
        new OA\Property(
            property: 'slot',
            description: 'Armor slot derived from the item classification (e.g. Arms, Core, Legs, Helmet).',
            type: 'string',
            example: 'Arms',
            nullable: true,
        ),
        new OA\Property(
            property: 'armor_type',
            description: 'Legacy armor type field, same as slot.',
            type: 'string',
            example: 'Arms',
            nullable: true,
            deprecated: true,
        ),
        new OA\Property(
            property: 'damage_resistance',
            properties: [
                new OA\Property(property: 'impact', type: 'double', example: 0.6925, nullable: true),
                new OA\Property(
                    property: 'physical',
                    properties: [
                        new OA\Property(property: 'multiplier', type: 'double', example: 0.7, nullable: true),
                        new OA\Property(property: 'threshold', type: 'double', example: 0, nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
                new OA\Property(
                    property: 'energy',
                    properties: [
                        new OA\Property(property: 'multiplier', type: 'double', example: 0.7, nullable: true),
                        new OA\Property(property: 'threshold', type: 'double', example: 0, nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
                new OA\Property(
                    property: 'distortion',
                    properties: [
                        new OA\Property(property: 'multiplier', type: 'double', example: 0.7, nullable: true),
                        new OA\Property(property: 'threshold', type: 'double', example: 0, nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
                new OA\Property(
                    property: 'thermal',
                    properties: [
                        new OA\Property(property: 'multiplier', type: 'double', example: 0.7, nullable: true),
                        new OA\Property(property: 'threshold', type: 'double', example: 0, nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
                new OA\Property(
                    property: 'biochemical',
                    properties: [
                        new OA\Property(property: 'multiplier', type: 'double', example: 0.7, nullable: true),
                        new OA\Property(property: 'threshold', type: 'double', example: 0, nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
                new OA\Property(
                    property: 'stun',
                    properties: [
                        new OA\Property(property: 'multiplier', type: 'double', example: 0.55, nullable: true),
                        new OA\Property(property: 'threshold', type: 'double', example: 0, nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
            ],
            type: 'object',
            nullable: true,
        ),
        new OA\Property(
            property: 'protected_body_parts',
            description: 'UUIDs of body parts covered by this armor.',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['b6d0be4f-bbdd-4a47-a168-8c9c9293c63a', 'e9c7c16d-d408-41c1-8d13-eec69cd2a013'],
            nullable: true
        ),
        new OA\Property(
            property: 'signature',
            description: 'Signature emissions produced by the armor.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'type', type: 'string', example: 'Electromagnetic', nullable: true),
                    new OA\Property(property: 'emission', type: 'double', example: 5, nullable: true),
                ],
                type: 'object'
            ),
            nullable: true
        ),
        new OA\Property(
            property: 'temperature_resistance',
            ref: '#/components/schemas/temperature_resistance',
            description: 'Temperature protection range from TemperatureResistance.',
            example: [
                'minimum' => -56,
                'maximum' => 86,
            ],
            nullable: true
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
            'temperature_resistance' => Arr::has($stdItem, 'TemperatureResistance')
                ? (new TemperatureResistanceResource(Arr::get($stdItem, 'TemperatureResistance')))->toArray($request)
                : null,
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
