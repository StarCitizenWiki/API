<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'clothing',
    title: 'Clothing',
    description: 'DEPRECATED: Use suit_armor instead. Wearable clothing like shirts, pants, gloves, etc., little to no damage reduction capabilities. Generated from SCItemClothingParams.',
    properties: [
        new OA\Property(
            property: 'clothing_type',
            description: 'Property is set if resource is of type "clothing". Superseded by type.',
            type: 'string',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'slot',
            description: 'Clothing slot derived from the item classification (e.g. Torso, Legs, Arms, Head).',
            type: 'string',
            example: 'Torso',
            nullable: true,
        ),
        new OA\Property(
            property: 'type',
            description: 'Clothing type, not actually set in the game data but derived from the item name.',
            type: 'string',
            example: 'T-Shirt',
            nullable: true,
        ),
        new OA\Property(
            property: 'resistances',
            description: 'Array of damage resistance entries for this clothing. Each entry includes damage type (physical, energy, distortion, thermal, biochemical, stun), damage multiplier (lower = more resistant), and damage threshold. Only damage types with defined multipliers are included.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'type', description: 'Damage type (physical, energy, distortion, thermal, biochemical, stun).', type: 'string', example: 'physical', nullable: true),
                    new OA\Property(property: 'multiplier', description: 'Damage multiplier for this resistance. Lower values mean more resistance.', type: 'double', example: 0.5, nullable: true),
                    new OA\Property(property: 'threshold', description: 'Damage threshold value for this resistance type.', type: 'double', example: 10.0, nullable: true),
                ],
                type: 'object'
            ),
            nullable: true,
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
            example: [
                'maximum_radiation_capacity' => 0,
                'radiation_dissipation_rate' => 0,
            ],
            nullable: true
        ),
        new OA\Property(
            property: 'gforce_resistance',
            description: 'G-force tolerance modifier from root. Positive increases tolerance, negative reduces it.',
            type: 'double',
            example: -0.125,
            nullable: true
        ),
    ],
    type: 'object',
    deprecated: true
)]
class ClothingResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        $resource = $this->resource;
        $classification = Arr::get($resource, 'classification', $this->resource->classification ?? null);
        $slot = $this->deriveSlot($classification);
        $type = $this->getType(
            Arr::get($resource, 'type', ''),
            Arr::get($resource, 'name', '')
        );
        $damageResistances = Arr::get($resource, 'data.stdItem.DamageResistances', Arr::get($resource, 'data.damageResistances', []));

        return [
            'clothing_type' => $type,
            'slot' => $slot,
            'type' => $type,
            'resistances' => $this->mapLegacyResistances($damageResistances),
            'temp_resistance_min' => Arr::get($resource, 'data.stdItem.TemperatureResistance.Minimum'),
            'temp_resistance_max' => Arr::get($resource, 'data.stdItem.TemperatureResistance.Maximum'),
            'radiation_resistance' => Arr::has($resource, 'data.stdItem.RadiationResistance')
                ? new RadiationResistanceResource(Arr::get($resource, 'data.stdItem.RadiationResistance'))->toArray($request)
                : null,
            'gforce_resistance' => Arr::get($resource, 'data.stdItem.GForceResistance.Value'),
        ];
    }

    private function getType(string $type, string $name): string
    {
        return match (true) {
            str_contains($name, 'T-Shirt'), str_contains($name, 'Shirt') !== false => 'T-Shirt',
            str_contains($name, 'Jacket') !== false => 'Jacket',
            str_contains($name, 'Gloves') !== false => 'Gloves',
            str_contains($name, 'Pants') !== false => 'Pants',
            str_contains($name, 'Bandana') !== false => 'Bandana',
            str_contains($name, 'Beanie') !== false => 'Beanie',
            str_contains($name, 'Boots') !== false => 'Boots',
            str_contains($name, 'Sweater') !== false => 'Sweater',
            str_contains($name, 'Hat') !== false => 'Hat',
            str_contains($name, 'Shoes') !== false => 'Shoes',
            str_contains($name, 'Head Cover') !== false => 'Head Cover',
            str_contains($name, 'Gown') !== false => 'Gown',
            str_contains($name, 'Slippers') !== false => 'Slippers',
            default => match (true) {
                str_contains($type, 'Backpack') !== false => 'Backpack',
                str_contains($type, 'Feet') !== false => 'Shoes',
                str_contains($type, 'Hands') !== false => 'Gloves',
                str_contains($type, 'Hat') !== false => 'Hat',
                str_contains($type, 'Legs') !== false => 'Pants',
                str_contains($type, 'Torso_0') !== false => 'Shirt',
                str_contains($type, 'Torso_1') !== false => 'Jacket',
                default => 'Unknown Type',
            },
        };

    }

    private function deriveSlot(?string $classification): ?string
    {
        if ($classification === null) {
            return null;
        }

        $parts = explode('.', $classification);

        return $parts !== [] ? Arr::last($parts) : null;
    }

    private function mapLegacyResistances(array $damageResistances): ?array
    {
        return collect([
            ['type' => 'physical', ...$this->mapDamageResistance($damageResistances, 'Physical')],
            ['type' => 'energy', ...$this->mapDamageResistance($damageResistances, 'Energy')],
            ['type' => 'distortion', ...$this->mapDamageResistance($damageResistances, 'Distortion')],
            ['type' => 'thermal', ...$this->mapDamageResistance($damageResistances, 'Thermal')],
            ['type' => 'biochemical', ...$this->mapDamageResistance($damageResistances, 'Biochemical')],
            ['type' => 'stun', ...$this->mapDamageResistance($damageResistances, 'Stun')],
        ])->filter(fn (array $entry) => isset($entry['multiplier']))->toArray();
    }

    private function mapDamageResistance(array $damageResistances, string $key): ?array
    {
        $entry = Arr::get($damageResistances, $key);

        if (! is_array($entry)) {
            return [];
        }

        return [
            'multiplier' => Arr::get($entry, 'Multiplier'),
            'threshold' => Arr::get($entry, 'Threshold'),
        ];
    }
}
