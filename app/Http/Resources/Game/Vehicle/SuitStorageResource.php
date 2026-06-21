<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_suit_storage',
    title: 'Suit Storage',
    description: 'Suit locker storage from ship data.',
    properties: [
        new OA\Property(property: 'lockers', description: 'Number of suit lockers.', type: 'integer', example: 1),
        new OA\Property(property: 'slots_total', description: 'Total suit slots across all lockers.', type: 'integer', example: 8),
        new OA\Property(
            property: 'by_locker',
            description: 'Per-locker breakdown, grouped by identical configuration.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'count', description: 'Number of identical lockers with this configuration.', type: 'integer', example: 1),
                    new OA\Property(property: 'name', description: 'Locker display name.', type: 'string', example: '<= PLACEHOLDER =>'),
                    new OA\Property(property: 'class_name', description: 'Locker class name.', type: 'string', example: 'locker_suit_drak_cutter_rambler'),
                    new OA\Property(property: 'port', description: 'Hardpoint port name.', type: 'string', example: 'hardpoint_suit_locker_expo'),
                    new OA\Property(property: 'slots_total', description: 'Total slots in this locker.', type: 'integer', example: 8),
                ],
                type: 'object'
            )
        ),
    ],
    type: 'object'
)]
class SuitStorageResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        $ss = $this->resource;
        $byLocker = $ss['ByLocker'] ?? [];

        $lockers = array_map(static fn (array $locker): array => [
            'name' => $locker['Name'] ?? null,
            'class_name' => $locker['ClassName'] ?? null,
            'port' => $locker['Port'] ?? null,
            'slots_total' => $locker['SlotsTotal'] ?? null,
        ], $byLocker);

        return [
            'lockers' => $ss['Lockers'] ?? null,
            'slots_total' => $ss['SlotsTotal'] ?? null,
            'by_locker' => $this->groupIdenticalLockers($lockers),
        ];
    }

    /**
     * Group identical locker configurations and add a count.
     *
     * Lockers with the same class_name and slots_total are collapsed into
     * a single entry with a count, keeping one representative port.
     *
     * @param  list<array<string, mixed>>  $lockers
     * @return list<array<string, mixed>>
     */
    private function groupIdenticalLockers(array $lockers): array
    {
        $grouped = [];

        foreach ($lockers as $locker) {
            $key = implode('|', [
                $locker['class_name'] ?? '',
                $locker['slots_total'] ?? 0,
            ]);

            if (isset($grouped[$key])) {
                $grouped[$key]['count']++;

                continue;
            }

            $locker['count'] = 1;
            $grouped[$key] = $locker;
        }

        return array_values($grouped);
    }
}
