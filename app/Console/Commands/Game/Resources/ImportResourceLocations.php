<?php

declare(strict_types=1);

namespace App\Console\Commands\Game\Resources;

use App\Enums\Game\ResourceKind;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Resource\ResourceData;
use App\Models\Game\Resource\ResourceLocation;
use App\Models\Game\Resource\ResourceProvider;
use App\Models\Game\StarmapLocationData;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use JsonException;

use function Laravel\Prompts\select;

class ImportResourceLocations extends Command implements PromptsForMissingInput
{
    protected $signature = 'game:import-resource-locations {version : Game version code to import} {--path=resources/locations.json : Relative path to the locations JSON file on the scunpacked disk}';

    protected $description = 'Import game resource locations for a specific game version';

    public function handle(): int
    {
        $versionCode = (string) $this->argument('version');
        $path = (string) $this->option('path');

        $gameVersion = GameVersion::query()
            ->where('code', $versionCode)
            ->first();

        if ($gameVersion === null) {
            $this->error(sprintf('Game version "%s" does not exist. Please create it first.', $versionCode));

            return self::FAILURE;
        }

        if (Storage::disk('scunpacked')->missing($path)) {
            $this->error(sprintf('%s not found in scunpacked storage.', $path));

            return self::FAILURE;
        }

        try {
            $contents = Storage::disk('scunpacked')->get($path);
            $payload = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->error(sprintf('Failed to decode %s: %s', $path, $exception->getMessage()));

            return self::FAILURE;
        }

        if (! is_array($payload)) {
            $this->error(sprintf('%s must contain an array of location providers.', $path));

            return self::FAILURE;
        }

        $resourceDataLookup = $this->buildResourceDataLookup($gameVersion->id);
        $starmapLookup = $this->buildStarmapLookup($gameVersion->id);
        $commodityLookup = $this->buildCommodityLookup();

        $imported = 0;
        $skipped = 0;
        $skippedResources = 0;

        $providerPlacements = [];
        $locationPlacements = [];

        foreach ($payload as $provider) {
            if (! is_array($provider)) {
                $skipped++;

                continue;
            }

            $locations = $provider['Locations'] ?? [];

            if (! is_array($locations)) {
                continue;
            }

            $areas = $provider['Areas'] ?? [];

            if (! is_array($areas)) {
                $areas = [];
            }

            $groups = $provider['Groups'] ?? [];

            if (! is_array($groups)) {
                continue;
            }

            $starmapLocationDataIds = [];

            foreach ($locations as $location) {
                if (! is_array($location)) {
                    continue;
                }

                $objectUuid = $this->normalizeString($location['Object'] ?? null);

                if ($objectUuid !== null) {
                    $starmapDataId = $starmapLookup->get($objectUuid);

                    if ($starmapDataId !== null) {
                        $starmapLocationDataIds[] = $starmapDataId;
                    }
                }
            }

            $starmapLocationDataIds = array_values(array_unique($starmapLocationDataIds));

            $areaData = $this->buildAreaData($areas);

            $providerName = $this->normalizeString($provider['Provider']['Name'] ?? null);

            $resourceProvider = ResourceProvider::query()->updateOrCreate(
                [
                    'game_version_id' => $gameVersion->id,
                    'provider_name' => $providerName,
                ],
                [
                    'areas' => $areaData,
                ],
            );

            if ($starmapLocationDataIds !== []) {
                $providerPlacements[$resourceProvider->id] = array_values(array_unique(array_merge(
                    $providerPlacements[$resourceProvider->id] ?? [],
                    $starmapLocationDataIds,
                )));
            }

            foreach ($groups as $group) {
                if (! is_array($group)) {
                    continue;
                }

                $groupName = $this->normalizeGroupName($this->normalizeString($group['GroupName'] ?? null) ?? 'Unknown');
                $groupProbability = round((float) ($group['GroupProbability'] ?? 0), 6);
                $deposits = $group['Deposits'] ?? [];

                if (! is_array($deposits)) {
                    continue;
                }

                foreach ($deposits as $deposit) {
                    if (! is_array($deposit)) {
                        continue;
                    }

                    $resourceDataId = $this->resolveResourceDataId($deposit, $resourceDataLookup);

                    if ($resourceDataId === null) {
                        $skippedResources++;

                        continue;
                    }

                    $rows = $this->buildLocationRows(
                        $resourceDataId,
                        $resourceProvider->id,
                        $groupName,
                        $groupProbability,
                        $deposit,
                        $locations,
                        $commodityLookup,
                    );

                    foreach ($rows as $row) {
                        $resourceLocation = ResourceLocation::query()->updateOrCreate(
                            [
                                'resource_data_id' => $row['resource_data_id'],
                                'resource_provider_id' => $row['resource_provider_id'],
                                'group_name' => $row['group_name'],
                                'relative_probability' => $row['relative_probability'],
                                'commodity_id' => $row['commodity_id'],
                                'quality_min' => $row['quality_min'],
                                'quality_max' => $row['quality_max'],
                                'min_percentage' => $row['min_percentage'],
                                'max_percentage' => $row['max_percentage'],
                            ],
                            $row,
                        );

                        if ($starmapLocationDataIds !== []) {
                            $locationPlacements[$resourceLocation->id] = array_values(array_unique(array_merge(
                                $locationPlacements[$resourceLocation->id] ?? [],
                                $starmapLocationDataIds,
                            )));
                        }

                        $imported++;
                    }
                }
            }
        }

        $syncedProviders = 0;
        foreach ($providerPlacements as $providerId => $starmapIds) {
            ResourceProvider::query()->find($providerId)?->starmapLocationData()->sync($starmapIds);
            $syncedProviders++;
        }

        $syncedLocations = 0;
        foreach ($locationPlacements as $locationId => $starmapIds) {
            ResourceLocation::query()->find($locationId)?->starmapLocationData()->sync($starmapIds);
            $syncedLocations++;
        }

        $this->info(sprintf(
            'Imported %d resource locations for version %s. Synced %d provider placements, %d location placements. Skipped %d invalid providers, %d unresolved resource UUIDs.',
            $imported,
            $gameVersion->code,
            $syncedProviders,
            $syncedLocations,
            $skipped,
            $skippedResources,
        ));

        return self::SUCCESS;
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'version' => function (): string {
                $options = GameVersion::query()
                    ->orderByDesc('released_at')
                    ->orderBy('code')
                    ->pluck('code', 'code')
                    ->toArray();

                if ($options === []) {
                    $this->error('No game versions exist. Please create one before importing.');

                    return '';
                }

                return select(
                    label: 'Select game version to import',
                    options: $options
                );
            },
        ];
    }

    /**
     * @return Collection<string, int>
     */
    private function buildResourceDataLookup(int $gameVersionId): Collection
    {
        return ResourceData::query()
            ->with('resource:id,uuid')
            ->where('game_version_id', $gameVersionId)
            ->get()
            ->mapWithKeys(fn (ResourceData $data): array => [$data->resource->uuid => $data->id]);
    }

    /**
     * @return Collection<string, int>
     */
    private function buildStarmapLookup(int $gameVersionId): Collection
    {
        return StarmapLocationData::query()
            ->with('location:id,uuid')
            ->where('game_version_id', $gameVersionId)
            ->get()
            ->mapWithKeys(fn (StarmapLocationData $data): array => [$data->location->uuid => $data->id]);
    }

    private function resolveResourceDataId(array $deposit, Collection $lookup): ?int
    {
        $resourceUuid = $this->normalizeString($deposit['ResourceUUID'] ?? null);

        if ($resourceUuid === null) {
            return null;
        }

        return $lookup->get($resourceUuid);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildLocationRows(
        int $resourceDataId,
        int $resourceProviderId,
        string $groupName,
        float $groupProbability,
        array $deposit,
        array $locations,
        Collection $commodityLookup,
    ): array {
        $relativeProbability = round((float) ($deposit['RelativeProbability'] ?? 0), 10);
        $resourceKind = ResourceKind::fromGroupName($groupName);
        $commodityKey = $this->normalizeString($deposit['ResourceKey'] ?? null);
        $locationData = [
            'locations' => collect($locations)->map(fn (array $loc): array => [
                'key' => $this->normalizeString($loc['Key'] ?? null),
                'object_uuid' => $this->normalizeString($loc['Object'] ?? null),
                'location_uuid' => $this->normalizeString($loc['Location'] ?? null),
                'tag' => $this->normalizeString($loc['Tag'] ?? null),
                'match_strategy' => $this->normalizeString($loc['MatchStrategy'] ?? null),
                'system' => $this->normalizeString($loc['System'] ?? null),
                'name' => $this->normalizeString($loc['Name'] ?? null),
                'type' => $this->normalizeString($loc['Type'] ?? null),
            ])->all(),
            'clustering' => $deposit['Clustering'] ?? null,
            'harvestable_setup' => $deposit['HarvestableSetup'] ?? null,
        ];

        $qualities = $deposit['ResourceQualities'] ?? null;

        if (is_array($qualities) && $qualities !== []) {
            return $this->explodeQualityRows(
                $resourceDataId,
                $resourceProviderId,
                $groupName,
                $groupProbability,
                $relativeProbability,
                $resourceKind,
                $commodityKey,
                $qualities,
                $locationData,
                $commodityLookup,
            );
        }

        return [[
            'resource_data_id' => $resourceDataId,
            'resource_provider_id' => $resourceProviderId,
            'group_name' => $groupName,
            'group_probability' => $groupProbability,
            'relative_probability' => $relativeProbability,
            'resource_kind' => $resourceKind,
            'commodity_id' => $commodityLookup->get($commodityKey),
            'quality_min' => null,
            'quality_max' => null,
            'quality_mean' => null,
            'quality_stddev' => null,
            'min_percentage' => null,
            'max_percentage' => null,
            'data' => $locationData,
        ]];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function explodeQualityRows(
        int $resourceDataId,
        int $resourceProviderId,
        string $groupName,
        float $groupProbability,
        float $relativeProbability,
        ResourceKind $resourceKind,
        ?string $commodityKey,
        array $qualities,
        array $locationData,
        Collection $commodityLookup,
    ): array {
        $rows = [];

        foreach ($qualities as $quality) {
            if (! is_array($quality)) {
                continue;
            }

            $qualityRange = $quality['QualityRange'] ?? $quality;

            $qualityCommodityKey = $this->normalizeString($quality['ResourceKey'] ?? null) ?? $commodityKey;

            $rows[] = [
                'resource_data_id' => $resourceDataId,
                'resource_provider_id' => $resourceProviderId,
                'group_name' => $groupName,
                'group_probability' => $groupProbability,
                'relative_probability' => $relativeProbability,
                'resource_kind' => $resourceKind,
                'commodity_id' => $commodityLookup->get($qualityCommodityKey),
                'quality_min' => isset($qualityRange['Min']) && is_numeric($qualityRange['Min']) ? (int) $qualityRange['Min'] : null,
                'quality_max' => isset($qualityRange['Max']) && is_numeric($qualityRange['Max']) ? (int) $qualityRange['Max'] : null,
                'quality_mean' => isset($qualityRange['Mean']) && is_numeric($qualityRange['Mean']) ? (int) $qualityRange['Mean'] : null,
                'quality_stddev' => isset($qualityRange['Stddev']) && is_numeric($qualityRange['Stddev']) ? (int) $qualityRange['Stddev'] : null,
                'min_percentage' => isset($quality['MinPercentage']) && is_numeric($quality['MinPercentage']) ? round((float) $quality['MinPercentage'], 4) : null,
                'max_percentage' => isset($quality['MaxPercentage']) && is_numeric($quality['MaxPercentage']) ? round((float) $quality['MaxPercentage'], 4) : null,
                'data' => $locationData,
            ];
        }

        return $rows;
    }

    private function buildCommodityLookup(): Collection
    {
        return Commodity::query()->pluck('id', 'key');
    }

    /**
     * @return array<int, mixed>|null
     */
    private function buildAreaData(array $areas): ?array
    {
        if ($areas === []) {
            return null;
        }

        $data = [];

        foreach ($areas as $area) {
            if (! is_array($area)) {
                continue;
            }

            $modifiers = $area['Modifiers'] ?? [];

            $data[] = [
                'name' => (string) ($area['Name'] ?? ''),
                'global_modifier' => isset($area['GlobalModifier']) && is_numeric($area['GlobalModifier'])
                    ? (float) $area['GlobalModifier']
                    : null,
                'modifiers' => is_array($modifiers) ? collect($modifiers)->map(static fn (array $m): array => [
                    'modifier' => (int) ($m['Modifier'] ?? 0),
                    'resource_uuid' => $m['ResourceUUID'] ?? null,
                    'group_name' => $m['GroupName'] ?? null,
                ])->all() : [],
            ];
        }

        return $data === [] ? null : $data;
    }

    private function normalizeGroupName(string $groupName): string
    {
        return match ($groupName) {
            'FPS mineables' => 'FPS_Mineables',
            'Havestables' => 'Harvestables',
            default => $groupName,
        };
    }

    private function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
