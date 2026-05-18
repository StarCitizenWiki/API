<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Models\Game\ItemData;
use App\Models\Game\VehicleData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ComputeBespokeItems implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 600;

    /**
     * Maximum number of vehicle families for Rule 2 (all-uneditable).
     * Handles cases like Origin 100-series (ORIG_100i, ORIG_125a, ORIG_135c)
     * which are 3 families but one shared platform.
     */
    private const int MAX_FAMILIES_UNEDITABLE = 3;

    /**
     * Maximum number of unique vehicles for Rule 3 (class-name match).
     */
    private const int MAX_VEHICLES_NAME_MATCH = 2;

    public function __construct(
        private readonly int $gameVersionId,
    ) {}

    public function handle(): void
    {
        // item installations from loadouts
        $installations = $this->collectInstallations();

        $vehicleNameSet = $this->buildVehicleNameSet();

        $bespokeItems = $this->classifyItems($installations, $vehicleNameSet);

        // items not in any loadout but whose class name references a vehicle
        $orphanBespoke = $this->classifyOrphanItems($installations, $vehicleNameSet);
        $bespokeItems = array_merge($bespokeItems, $orphanBespoke);

        $this->persist($bespokeItems);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Compute bespoke items job failed', [
            'game_version_id' => $this->gameVersionId,
            'message' => $exception->getMessage(),
        ]);
    }

    /**
     * Walk all vehicle loadouts and collect every item installation with its vehicle context.
     *
     * @return array<string, array<int, array{vehicle: string, editable: bool}>>
     */
    private function collectInstallations(): array
    {
        $installations = [];

        $vehicles = VehicleData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->select(['class_name', 'data'])
            ->get();

        foreach ($vehicles as $vehicle) {
            $loadout = $vehicle->data->get('Loadout', []) ?? [];

            $this->walkLoadout(
                is_array($loadout) ? $loadout : $loadout->toArray(),
                $installations,
                $vehicle->class_name,
            );
        }

        return $installations;
    }

    /**
     * Recursively walk a loadout tree, collecting item installations.
     *
     * @param  array<int, array<string, mixed>>  $loadout
     * @param  array<string, array<int, array{vehicle: string, editable: bool}>>  $installations
     */
    private function walkLoadout(array $loadout, array &$installations, string $vehicleClassName): void
    {
        foreach ($loadout as $port) {
            $className = $port['ClassName'] ?? '';
            $editable = $port['Editable'] ?? true;

            if ($className !== '' && $className !== 'None') {
                if (! isset($installations[$className])) {
                    $installations[$className] = [];
                }

                $installations[$className][] = [
                    'vehicle' => $vehicleClassName,
                    'editable' => (bool) $editable,
                ];
            }

            $children = $port['Loadout'] ?? [];
            if (is_array($children) && $children !== []) {
                $this->walkLoadout($children, $installations, $vehicleClassName);
            }
        }
    }

    /**
     * Build a set of vehicle identity strings for class-name matching.
     * Includes both full class names and MANUF_Model family prefixes, sorted longest-first for greedy matching.
     *
     * @return list<string>
     */
    private function buildVehicleNameSet(): array
    {
        $vehicleClassNames = VehicleData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->pluck('class_name')
            ->unique()
            ->values()
            ->all();

        $names = [];
        foreach ($vehicleClassNames as $className) {
            // Full class name (e.g. "MISC_Hull_B")
            $names[] = $className;

            // Family prefix: first 2 underscore-delimited parts (e.g. "MISC_Hull_B" _> "MISC_Hull")
            $family = $this->extractFamily($className);
            if ($family !== $className) {
                $names[] = $family;
            }
        }

        $names = array_unique(array_filter($names, static fn (string $n): bool => strlen($n) > 4));

        // Sort longest-first for greedy matching
        usort($names, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        return $names;
    }

    /**
     * Extract the vehicle family prefix from a class name.
     * e.g. "AEGS_Vanguard_Harbinger" -> "AEGS_Vanguard"
     * e.g. "MISC_Hull_B" -> "MISC_Hull"
     * e.g. "RSI_Aurora_Mk2" > "RSI_Aurora"
     */
    private function extractFamily(string $vehicleClassName): string
    {
        $parts = explode('_', $vehicleClassName);

        if (count($parts) >= 2) {
            return $parts[0].'_'.$parts[1];
        }

        return $vehicleClassName;
    }

    /**
     * Classify each installed item as bespoke or universal.
     *
     * @param  array<string, array<int, array{vehicle: string, editable: bool}>>  $installations
     * @param  list<string>  $vehicleNameSet
     * @return array<string, array{is_bespoke: bool, bespoke_vehicle_tags: list<string>}>
     */
    private function classifyItems(array $installations, array $vehicleNameSet): array
    {
        $classNames = array_keys($installations);

        $items = ItemData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->whereIn('class_name', $classNames)
            ->get()
            ->keyBy('class_name');

        $results = [];

        foreach ($installations as $className => $installs) {
            $item = $items->get($className);

            if ($item === null) {
                continue;
            }

            $requiredTags = $item->data['stdItem']['RequiredTags'] ?? [];
            $hasRequiredTags = is_array($requiredTags) && $requiredTags !== [];

            $vehicles = array_unique(array_column($installs, 'vehicle'));
            $families = array_unique(array_map($this->extractFamily(...), $vehicles));
            $allUneditable = ! in_array(true, array_column($installs, 'editable'), true);

            $isBespoke = false;

            // Has RequiredTags = bespoke
            if ($hasRequiredTags) {
                $isBespoke = true;
            }

            // OR: All-uneditable + <=N vehicle families = bespoke
            if (! $isBespoke && $allUneditable && count($families) <= self::MAX_FAMILIES_UNEDITABLE) {
                $isBespoke = true;
            }

            // OR: Class-name matches a vehicle name + <=N unique vehicles = bespoke
            if (! $isBespoke && count($vehicles) <= self::MAX_VEHICLES_NAME_MATCH) {
                // Check contiguous vehicle name match (e.g. 'VNCL_Mauler' in 'VNCL_Mauler_Seat')
                foreach ($vehicleNameSet as $vehicleName) {
                    if (Str::contains($className, $vehicleName)) {
                        $isBespoke = true;

                        break;
                    }
                }

                // Also check model-name token match.
                // CIG naming often embeds just the model name without manufacturer prefix or interleaves other tokens (e.g. 'COOL_VNCL_S04_Mauler' has model 'Mauler' but 'VNCL_Mauler' is not contiguous).
                if (! $isBespoke) {
                    $classNameTokens = explode('_', $className);

                    foreach ($vehicles as $vehicleClassName) {
                        $parts = explode('_', $vehicleClassName);

                        if (count($parts) >= 2 && strlen($parts[1]) >= 5 && in_array($parts[1], $classNameTokens, true)) {
                            $isBespoke = true;

                            break;
                        }
                    }
                }
            }

            // Derive bespoke_vehicle_tags: all prefix levels of vehicle class names to match any tag the vehicle's port_tags might contain.
            // e.g. vehicles [MISC_Hull_B] > tags ["MISC_Hull", "MISC_Hull_B"]
            // e.g. vehicles [AEGS_Vanguard_Harbinger, AEGS_Vanguard_Sentinel] > tags ["AEGS_Vanguard", "AEGS_Vanguard_Harbinger", "AEGS_Vanguard_Sentinel"]
            $bespokeVehicleTags = $isBespoke ? $this->deriveVehicleTags($vehicles) : [];

            $results[$className] = [
                'is_bespoke' => $isBespoke,
                'bespoke_vehicle_tags' => $bespokeVehicleTags,
            ];
        }

        return $results;
    }

    /**
     * Derive vehicle identity tags from the class names of vehicles an item is installed on.
     * Returns all prefix levels so that any tag from the vehicle's port_tags will match.
     *
     * e.g. ['MISC_Hull_B'] > ['MISC_Hull', 'MISC_Hull_B']
     * e.g. ['AEGS_Vanguard_Harbinger', 'AEGS_Vanguard_Sentinel'] > ['AEGS_Vanguard', 'AEGS_Vanguard_Harbinger', 'AEGS_Vanguard_Sentinel']
     *
     * @param  list<string>  $vehicleClassNames
     * @return list<string>
     */
    private function deriveVehicleTags(array $vehicleClassNames): array
    {
        $tags = [];

        foreach ($vehicleClassNames as $className) {
            $parts = explode('_', $className);

            // Build up all prefix levels: 'MISC', 'MISC_Hull', 'MISC_Hull_B'
            for ($i = 1, $iMax = count($parts); $i <= $iMax; $i++) {
                $prefix = implode('_', array_slice($parts, 0, $i));

                if (strlen($prefix) > 4) { // Skip manufacturer-only prefixes (4 chars like 'AEGS', 'MISC')
                    $tags[] = $prefix;
                }
            }
        }

        return array_values(array_unique($tags));
    }

    /**
     * Classify items NOT in any vehicle loadout but whose class name contains a vehicle reference.
     * These are ship-specific parts that happen not to be installed on any vehicle in the current game data.
     *
     * e.g. 'Mount_Gimbal_S3_315p' is the 315p gimbal mount but not in any loadout
     * e.g. 'VNCL_Mauler_Remote_Turret_S6_Double' is a Mauler turret but not installed
     *
     * @param  array<string, array<int, array{vehicle: string, editable: bool}>>  $installations
     * @param  list<string>  $vehicleNameSet
     * @return array<string, array{is_bespoke: bool, bespoke_vehicle_tags: list<string>}>
     */
    private function classifyOrphanItems(array $installations, array $vehicleNameSet): array
    {
        $installedClassNames = array_keys($installations);

        $allItemClassNames = ItemData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->where('is_player_relevant', true)
            ->whereNotIn('class_name', $installedClassNames)
            ->pluck('class_name')
            ->values()
            ->all();

        $modelTokenToVehicles = [];
        $vehicleClassNames = VehicleData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->pluck('class_name')
            ->unique()
            ->values()
            ->all();

        foreach ($vehicleClassNames as $vcn) {
            $parts = explode('_', $vcn);

            if (count($parts) >= 2 && strlen($parts[1]) >= 4) {
                $model = $parts[1];

                if (! isset($modelTokenToVehicles[$model])) {
                    $modelTokenToVehicles[$model] = [];
                }

                $modelTokenToVehicles[$model][] = $vcn;
            }
        }

        $results = [];

        foreach ($allItemClassNames as $className) {
            $matchedVehicles = [];

            foreach ($vehicleNameSet as $vehicleName) {
                if (Str::contains($className, $vehicleName)) {
                    // Find which vehicle(s) this name belongs to
                    foreach ($vehicleClassNames as $vcn) {
                        if (Str::contains($vcn, $vehicleName) && Str::contains($className, $vehicleName)) {
                            $matchedVehicles[$vcn] = true;
                        }
                    }
                }
            }

            // Check model-name token match
            $tokens = explode('_', $className);
            foreach ($modelTokenToVehicles as $model => $vcns) {
                if (in_array($model, $tokens, true)) {
                    foreach ($vcns as $vcn) {
                        $matchedVehicles[$vcn] = true;
                    }
                }
            }

            if ($matchedVehicles !== []) {
                $vehicleNames = array_keys($matchedVehicles);
                $results[$className] = [
                    'is_bespoke' => true,
                    'bespoke_vehicle_tags' => $this->deriveVehicleTags($vehicleNames),
                ];
            }
        }

        return $results;
    }

    /**
     * Persist the bespoke classification to the database.
     *
     * @param  array<string, array{is_bespoke: bool, bespoke_vehicle_tags: list<string>}>  $bespokeItems
     */
    private function persist(array $bespokeItems): void
    {
        DB::transaction(function () use ($bespokeItems): void {
            ItemData::query()
                ->where('game_version_id', $this->gameVersionId)
                ->update([
                    'is_bespoke' => false,
                    'bespoke_vehicle_tags' => null,
                ]);

            $bespokeEntries = array_filter($bespokeItems, static fn (array $item): bool => $item['is_bespoke']);

            $byTags = [];

            foreach ($bespokeEntries as $className => $data) {
                $key = json_encode($data['bespoke_vehicle_tags'], JSON_THROW_ON_ERROR);
                $byTags[$key][] = $className;
            }

            foreach ($byTags as $tagJson => $classNames) {
                $tags = json_decode($tagJson, true, 512, JSON_THROW_ON_ERROR);

                foreach (array_chunk($classNames, 500) as $chunk) {
                    ItemData::query()
                        ->where('game_version_id', $this->gameVersionId)
                        ->whereIn('class_name', $chunk)
                        ->update([
                            'is_bespoke' => true,
                            'bespoke_vehicle_tags' => $tags,
                        ]);
                }
            }
        });

        $bespokeCount = count(array_filter($bespokeItems, static fn (array $item): bool => $item['is_bespoke']));

        Log::info('Computed bespoke items', [
            'game_version_id' => $this->gameVersionId,
            'total_installed' => count($bespokeItems),
            'bespoke_count' => $bespokeCount,
        ]);
    }
}
