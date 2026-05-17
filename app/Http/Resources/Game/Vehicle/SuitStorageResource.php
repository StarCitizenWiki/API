<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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

        $byLocker = collect(Arr::get($ss, 'ByLocker', []))
            ->map(static fn (array $locker): array => [
                'name' => Arr::get($locker, 'Name'),
                'class_name' => Arr::get($locker, 'ClassName'),
                'port' => Arr::get($locker, 'Port'),
                'slots_total' => Arr::get($locker, 'SlotsTotal'),
            ]);

        return [
            'lockers' => Arr::get($ss, 'Lockers'),
            'slots_total' => Arr::get($ss, 'SlotsTotal'),
            'by_locker' => $this->groupIdenticalLockers($byLocker),
        ];
    }

    /**
     * Group identical locker configurations and add a count.
     *
     * Lockers with the same class_name and slots_total are collapsed into
     * a single entry with a count, keeping one representative port.
     *
     * @param  Collection<int, array<string, mixed>>  $lockers
     * @return list<array<string, mixed>>
     */
    private function groupIdenticalLockers($lockers): array
    {
        return $lockers
            ->groupBy(static fn (array $locker): string => implode('|', [
                $locker['class_name'] ?? '',
                $locker['slots_total'] ?? 0,
            ]))
            ->map(static fn ($group): array => tap($group->first(), static function (array &$locker) use ($group): void {
                $locker['count'] = $group->count();
            }))
            ->values()
            ->all();
    }
}
