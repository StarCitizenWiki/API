<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Models\Game\ItemData;
use App\Models\Game\VehicleData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class SyncVehicleInstalledItems implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly int $gameVersionId) {}

    public function handle(): void
    {
        VehicleData::query()->where('game_version_id', $this->gameVersionId)
            ->chunkById(500, function ($vehicles): void {
                foreach ($vehicles as $vehicle) {
                    self::syncVehicle($vehicle);
                }
            });
    }

    public static function syncVehicle(VehicleData $vehicle): void
    {
        DB::transaction(function () use ($vehicle): void {
            $vehicle = VehicleData::query()->lockForUpdate()->findOrFail($vehicle->id);
            $uuids = self::extractPortUuids($vehicle->data['Loadout'] ?? []);
            $ids = ItemData::query()
                ->where('game_version_id', $vehicle->game_version_id)
                ->whereHas('item', fn ($query) => $query->whereIn('uuid', $uuids))
                ->pluck('id');

            $vehicle->installedItems()->sync($ids);
        });
    }

    private static function extractPortUuids(array $loadout): array
    {
        $uuids = [];
        foreach ($loadout as $port) {
            $uuid = $port['UUID'] ?? null;
            if (is_string($uuid) && $uuid !== '') {
                $uuids[] = $uuid;
            }
            if (isset($port['Loadout']) && is_array($port['Loadout'])) {
                $uuids = array_merge($uuids, self::extractPortUuids($port['Loadout']));
            }
        }

        return array_unique(array_filter($uuids));
    }
}
