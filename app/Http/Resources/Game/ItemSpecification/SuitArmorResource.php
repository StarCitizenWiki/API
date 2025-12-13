<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'suit_armor',
    title: 'Suit Armor',
    description: 'Protective characteristics of wearable armor pieces.',
    properties: [
        new OA\Property(
            property: 'damage_resistance',
            properties: [
                new OA\Property(property: 'impact', type: 'double', nullable: true),
                new OA\Property(
                    property: 'physical',
                    properties: [
                        new OA\Property(property: 'multiplier', type: 'double', nullable: true),
                        new OA\Property(property: 'threshold', type: 'double', nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
                new OA\Property(
                    property: 'energy',
                    properties: [
                        new OA\Property(property: 'multiplier', type: 'double', nullable: true),
                        new OA\Property(property: 'threshold', type: 'double', nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
                new OA\Property(
                    property: 'distortion',
                    properties: [
                        new OA\Property(property: 'multiplier', type: 'double', nullable: true),
                        new OA\Property(property: 'threshold', type: 'double', nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
                new OA\Property(
                    property: 'thermal',
                    properties: [
                        new OA\Property(property: 'multiplier', type: 'double', nullable: true),
                        new OA\Property(property: 'threshold', type: 'double', nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
                new OA\Property(
                    property: 'biochemical',
                    properties: [
                        new OA\Property(property: 'multiplier', type: 'double', nullable: true),
                        new OA\Property(property: 'threshold', type: 'double', nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
                new OA\Property(
                    property: 'stun',
                    properties: [
                        new OA\Property(property: 'multiplier', type: 'double', nullable: true),
                        new OA\Property(property: 'threshold', type: 'double', nullable: true),
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
            nullable: true
        ),
        new OA\Property(
            property: 'signature',
            description: 'Signature emissions produced by the armor.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'type', type: 'string', nullable: true),
                    new OA\Property(property: 'emission', type: 'double', nullable: true),
                ],
                type: 'object'
            ),
            nullable: true
        ),
        new OA\Property(property: 'temperature_resistance', ref: '#/components/schemas/temperature_resistance', nullable: true),
        new OA\Property(property: 'radiation_resistance', ref: '#/components/schemas/radiation_resistance', nullable: true),
    ],
    type: 'object'
)]
class SuitArmorResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $armor = Arr::get($data, 'stdItem.SuitArmor', []);
        $damageResistance = Arr::get($armor, 'DamageResistance', []);

        return [
            'damage_resistance' => [
                'impact' => Arr::get($damageResistance, 'Impact'),
                'physical' => $this->mapTypeResistance($damageResistance, 'Physical'),
                'energy' => $this->mapTypeResistance($damageResistance, 'Energy'),
                'distortion' => $this->mapTypeResistance($damageResistance, 'Distortion'),
                'thermal' => $this->mapTypeResistance($damageResistance, 'Thermal'),
                'biochemical' => $this->mapTypeResistance($damageResistance, 'Biochemical'),
                'stun' => $this->mapTypeResistance($damageResistance, 'Stun'),
            ],
            'protected_body_parts' => Arr::get($armor, 'ProtectedBodyParts', []),
            'signature' => collect(Arr::get($armor, 'Signature', []))
                ->map(fn (array $signature) => [
                    'type' => Arr::get($signature, 'Signature'),
                    'emission' => Arr::get($signature, 'Emission'),
                ])
                ->values()
                ->toArray(),
            'temperature_resistance' => Arr::has($data, 'stdItem.TemperatureResistance')
                ? (new TemperatureResistanceResource(Arr::get($data, 'stdItem.TemperatureResistance')))->toArray($request)
                : null,
            'radiation_resistance' => Arr::has($data, 'stdItem.RadiationResistance')
                ? (new RadiationResistanceResource(Arr::get($data, 'stdItem.RadiationResistance')))->toArray($request)
                : null,
        ];
    }

    private function mapTypeResistance(array $damageResistance, string $key): ?array
    {
        if (! Arr::has($damageResistance, $key)) {
            return null;
        }

        return [
            'multiplier' => Arr::get($damageResistance, "{$key}.Multiplier"),
            'threshold' => Arr::get($damageResistance, "{$key}.Threshold"),
        ];
    }
}
