<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_weapon_storage',
    title: 'Weapon Storage',
    description: 'Weapon locker / rack storage from ship data.',
    properties: [
        new OA\Property(property: 'lockers', description: 'Number of weapon lockers.', type: 'integer', example: 2),
        new OA\Property(property: 'slots_total', description: 'Total weapon slots across all lockers.', type: 'integer', example: 40),
        new OA\Property(property: 'slots_rifle', description: 'Total rifle slots across all lockers.', type: 'integer', example: 16),
        new OA\Property(property: 'slots_pistol', description: 'Total pistol slots across all lockers.', type: 'integer', example: 24),
        new OA\Property(
            property: 'by_locker',
            description: 'Per-locker breakdown, grouped by identical configuration.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'count', description: 'Number of identical racks with this configuration.', type: 'integer', example: 2),
                    new OA\Property(property: 'name', description: 'Locker display name.', type: 'string', example: 'Weapon Rack'),
                    new OA\Property(property: 'class_name', description: 'Locker class name.', type: 'string', example: 'weapon_rack_anvl_carrack_armory_large'),
                    new OA\Property(property: 'port', description: 'Hardpoint port name.', type: 'string', example: 'hardpoint_weapon_locker_01'),
                    new OA\Property(property: 'slots_total', description: 'Total slots in this locker.', type: 'integer', example: 20),
                    new OA\Property(property: 'slots_rifle', description: 'Rifle slots in this locker.', type: 'integer', example: 8),
                    new OA\Property(property: 'slots_pistol', description: 'Pistol slots in this locker.', type: 'integer', example: 12),
                ],
                type: 'object'
            )
        ),
    ],
    type: 'object'
)]
class WeaponStorageResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        $ws = $this->resource;

        $byLocker = collect(Arr::get($ws, 'ByLocker', []))
            ->map(static fn (array $locker): array => [
                'name' => Arr::get($locker, 'Name'),
                'class_name' => Arr::get($locker, 'ClassName'),
                'port' => Arr::get($locker, 'Port'),
                'slots_total' => Arr::get($locker, 'SlotsTotal'),
                'slots_rifle' => Arr::get($locker, 'SlotsRifle'),
                'slots_pistol' => Arr::get($locker, 'SlotsPistol'),
            ]);

        return [
            'lockers' => Arr::get($ws, 'Lockers'),
            'slots_total' => Arr::get($ws, 'SlotsTotal'),
            'slots_rifle' => Arr::get($ws, 'SlotsRifle'),
            'slots_pistol' => Arr::get($ws, 'SlotsPistol'),
            'by_locker' => $this->groupIdenticalRacks($byLocker),
        ];
    }

    /**
     * Group identical rack configurations and add a count.
     *
     * Racks with the same class_name, slots_total, slots_rifle, and slots_pistol
     * are collapsed into a single entry with a count, keeping one representative port.
     *
     * @param  Collection<int, array<string, mixed>>  $racks
     * @return list<array<string, mixed>>
     */
    private function groupIdenticalRacks($racks): array
    {
        return $racks
            ->groupBy(static fn (array $rack): string => implode('|', [
                $rack['class_name'] ?? '',
                $rack['slots_total'] ?? 0,
                $rack['slots_rifle'] ?? 0,
                $rack['slots_pistol'] ?? 0,
            ]))
            ->map(static fn ($group): array => tap($group->first(), static function (array &$rack) use ($group): void {
                $rack['count'] = $group->count();
            }))
            ->values()
            ->all();
    }
}
