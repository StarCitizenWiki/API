<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'weapon_rack',
    title: 'Weapon Rack',
    description: 'Summary of weapon slots on a weapon rack item, grouped by weapon category.',
    properties: [
        new OA\Property(property: 'pistols', description: 'Number of small weapon slots.', type: 'integer', example: 12),
        new OA\Property(property: 'rifles', description: 'Number of medium and large weapon slots.', type: 'integer', example: 8),
        new OA\Property(property: 'gadgets', description: 'Number of gadget slots.', type: 'integer', example: 2),
        new OA\Property(property: 'total_weapon_slots', description: 'Total number of weapon slots across all categories.', type: 'integer', example: 22),
    ],
    type: 'object'
)]
class WeaponRackResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $ports = $this->extractPorts($this->resource);

        $pistols = 0;
        $rifles = 0;
        $gadgets = 0;

        foreach ($ports as $port) {
            $types = $this->normalizePortTypes($port);
            $maxSize = Arr::get($port, 'MaxSize', 0);

            if (! $this->isWeaponPort($types)) {
                continue;
            }

            if ($this->isGadgetSlot($types)) {
                $gadgets++;

                continue;
            }

            if ($maxSize <= 1) {
                $pistols++;
            } else {
                $rifles++;
            }
        }

        return [
            'pistols' => $pistols,
            'rifles' => $rifles,
            'gadgets' => $gadgets,
            'total_weapon_slots' => $pistols + $rifles + $gadgets,
        ];
    }

    /**
     * Normalize CompatibleTypes and legacy Types values into comparable strings.
     *
     * @param  array<string, mixed>  $port
     * @return list<string>
     */
    private function normalizePortTypes(array $port): array
    {
        $compatibleTypes = Arr::get($port, 'CompatibleTypes', []);

        if (is_array($compatibleTypes) && $compatibleTypes !== []) {
            return collect($compatibleTypes)
                ->flatMap(static function (array $compatibleType): array {
                    $type = Arr::get($compatibleType, 'Type');
                    $subTypes = Arr::get($compatibleType, 'SubTypes', []);

                    if (! is_string($type) || $type === '') {
                        return [];
                    }

                    $normalized = [$type];

                    if (is_array($subTypes)) {
                        foreach ($subTypes as $subType) {
                            if (is_string($subType) && $subType !== '') {
                                $normalized[] = "{$type}.{$subType}";
                            }
                        }
                    }

                    return $normalized;
                })
                ->values()
                ->all();
        }

        $types = Arr::get($port, 'Types', []);

        return is_array($types)
            ? array_values(array_filter($types, is_string(...)))
            : [];
    }

    /**
     * Check if the port accepts any WeaponPersonal type.
     */
    private function isWeaponPort(array $types): bool
    {
        foreach ($types as $type) {
            if (str_starts_with($type, 'WeaponPersonal')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the port is a gadget-only slot (WeaponPersonal.Gadget).
     */
    private function isGadgetSlot(array $types): bool
    {
        foreach ($types as $type) {
            if (str_starts_with($type, 'WeaponPersonal.Gadget')) {
                return true;
            }
        }

        return false;
    }
}
