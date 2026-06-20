<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Vehicle\Concerns\CategorizesEquipmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_relay_entry',
    title: 'Relay Entry',
    description: 'A single relay junction with its connected hardpoints grouped by equipment category.',
    properties: [
        new OA\Property(property: 'hardpoint', description: 'Relay hardpoint name.', type: 'string', example: 'hardpoint_relay_bridge'),
        new OA\Property(property: 'class_name', description: 'Relay entity class name.', type: 'string', example: 'RELAY_2slot', nullable: true),
        new OA\Property(property: 'fuse_slots', description: 'Number of fuse slots on this relay.', type: 'integer', example: 2),
        new OA\Property(property: 'room', description: 'Room/bone name where the relay is located.', type: 'string', example: 'animated_bridge', nullable: true),
        new OA\Property(property: 'connection_count', description: 'Total number of connected hardpoints.', type: 'integer', example: 26),
        new OA\Property(
            property: 'connected_hardpoints',
            description: 'Hardpoints powered by this relay, grouped by equipment category.',
            type: 'array',
            items: new OA\Items(
                description: 'A group of connected hardpoints sharing the same equipment category.',
                properties: [
                    new OA\Property(property: 'category', description: 'Equipment category label.', type: 'string', example: 'Weapons'),
                    new OA\Property(property: 'count', description: 'Number of hardpoints in this group.', type: 'integer', example: 4),
                    new OA\Property(
                        property: 'items',
                        description: 'Individual hardpoints in this group.',
                        type: 'array',
                        items: new OA\Items(
                            description: 'A hardpoint connected to this relay.',
                            properties: [
                                new OA\Property(property: 'hardpoint', description: 'Hardpoint name.', type: 'string', example: 'hardpoint_shield_generator_l'),
                                new OA\Property(property: 'item_name', description: 'Display name of the installed item.', type: 'string', example: 'Barbican', nullable: true),
                                new OA\Property(property: 'class_name', description: 'SC class name of the installed item.', type: 'string', example: 'SHLD_BASL_S03_Barbican_SCItem', nullable: true),
                                new OA\Property(property: 'type', description: 'Primary equipment type.', type: 'string', example: 'Shield', nullable: true),
                                new OA\Property(property: 'uuid', description: 'Item UUID.', type: 'string', nullable: true),
                            ],
                            type: 'object',
                        ),
                    ),
                ],
                type: 'object',
            ),
            nullable: true,
        ),
    ],
    type: 'object'
)]
class RelayEntryResource extends AbstractBaseResource
{
    use CategorizesEquipmentType;

    public function toArray(Request $request): array
    {
        $res = $this->resource;
        $connected = $res['ConnectedHardpoints'] ?? [];

        return [
            'hardpoint' => $res['HardpointName'] ?? null,
            'class_name' => $res['ClassName'] ?? null,
            'fuse_slots' => $res['FuseSlots'] ?? 0,
            'room' => $res['Room'] ?? null,
            'connection_count' => count($connected),
            'connected_hardpoints' => $this->buildGroupedHardpoints($connected),
        ];
    }

    /**
     * Group connected hardpoints by equipment category.
     */
    private function buildGroupedHardpoints(array $connected): array
    {
        $groups = [];

        foreach ($connected as $hp) {
            $normalized = $this->normalizeHardpoint($hp);

            if ($normalized === null) {
                continue;
            }

            $groups[$this->categorizeHardpoint($normalized)][] = $normalized;
        }

        $result = [];

        foreach ($groups as $category => $items) {
            $result[] = [
                'category' => $category,
                'count' => count($items),
                'items' => array_values($items),
            ];
        }

        return $result;
    }

    /**
     * Normalize a connected hardpoint entry into a consistent shape.
     */
    private function normalizeHardpoint(array|string $hp): ?array
    {
        if (is_string($hp)) {
            return ['hardpoint' => $hp];
        }

        $hardpointName = $hp['HardpointName'] ?? null;

        if ($hardpointName === null) {
            return null;
        }

        $type = $hp['Type'] ?? null;
        $primaryType = $type !== null ? Str::before($type, '.') : null;

        return [
            'hardpoint' => $hardpointName,
            'item_name' => $hp['ItemName'] ?? null,
            'class_name' => $hp['ClassName'] ?? null,
            'type' => $primaryType,
            'uuid' => $hp['UUID'] ?? null,
        ];
    }

    /**
     * Assign a display category using the shared trait, with hardpoint-name fallback.
     */
    private function categorizeHardpoint(array $hp): string
    {
        $type = $hp['type'] ?? null;

        if ($type !== null) {
            return $this->categorizeEquipmentType($type);
        }

        return $this->categorizeByHardpointName($hp['hardpoint']);
    }
}
