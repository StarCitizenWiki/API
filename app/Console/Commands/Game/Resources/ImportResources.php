<?php

declare(strict_types=1);

namespace App\Console\Commands\Game\Resources;

use App\Enums\Game\ResourceKind;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Resource\Resource;
use App\Models\Game\Resource\ResourceData;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use JsonException;

use function Laravel\Prompts\select;

class ImportResources extends Command implements PromptsForMissingInput
{
    protected $signature = 'game:import-resources {version : Game version code to import} {--path=resources/resources.json : Relative path to the resources JSON file on the scunpacked disk}';

    protected $description = 'Import game resources for a specific game version';

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
            $this->error(sprintf('%s must contain an array of resources.', $path));

            return self::FAILURE;
        }

        $imported = 0;
        $newResources = 0;
        $existingResources = 0;
        $skipped = 0;
        $linkedCommodities = 0;
        $skippedCommodityLinks = 0;

        $commodityLookup = Commodity::query()
            ->select(['id', 'uuid'])
            ->get()
            ->keyBy('uuid');

        foreach ($payload as $resourcePayload) {
            if (! is_array($resourcePayload) || ! $this->isValidResourcePayload($resourcePayload)) {
                $skipped++;

                continue;
            }

            $resource = Resource::query()->firstOrCreate(
                ['uuid' => (string) $resourcePayload['UUID']],
                ['uuid' => (string) $resourcePayload['UUID']]
            );

            if ($resource->wasRecentlyCreated) {
                $newResources++;
            } else {
                $existingResources++;
            }

            $resourceData = ResourceData::query()->updateOrCreate(
                [
                    'resource_id' => $resource->id,
                    'game_version_id' => $gameVersion->id,
                ],
                [
                    'key' => (string) $resourcePayload['Key'],
                    'name' => (string) ($resourcePayload['Name'] ?? ''),
                    'kind' => ResourceKind::fromRawKind((string) $resourcePayload['Kind']),
                    'tier' => $resourcePayload['Tier'] ?? null,
                    'signature' => isset($resourcePayload['Signature']) && is_numeric($resourcePayload['Signature'])
                        ? (int) $resourcePayload['Signature']
                        : null,
                    'data' => $resourcePayload,
                ]
            );

            $commodityRows = $this->buildCommoditySyncData($resourcePayload, $commodityLookup, $skippedCommodityLinks);

            if ($commodityRows !== []) {
                $linkedCommodities += count($commodityRows);
            }

            $resourceData->commodities()->detach();

            foreach ($commodityRows as $row) {
                $resourceData->commodities()->attach($row['commodity_id'], Arr::except($row, 'commodity_id'));
            }

            $imported++;
        }

        $this->info(sprintf(
            'Imported %d resources for version %s (%d new, %d existing). Skipped %d invalid. Linked %d commodities (%d links skipped).',
            $imported,
            $gameVersion->code,
            $newResources,
            $existingResources,
            $skipped,
            $linkedCommodities,
            $skippedCommodityLinks
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
     * @return array<int, array{commodity_id: int, weight?: float|null, min_percentage?: float|null, max_percentage?: float|null, probability?: float|null, quality_scale?: float|null, curve_exponent?: float|null}>
     */
    private function buildCommoditySyncData(array $resourcePayload, $commodityLookup, int &$skippedCommodityLinks): array
    {
        $rows = [];

        $compositionParts = Arr::get($resourcePayload, 'Composition.Parts', []);

        if (is_array($compositionParts)) {
            foreach ($compositionParts as $part) {
                if (! is_array($part)) {
                    continue;
                }

                $commodityUuid = $part['ResourceTypeUUID'] ?? null;

                if ($commodityUuid === null) {
                    continue;
                }

                $commodity = $commodityLookup->get($commodityUuid);

                if ($commodity === null) {
                    $skippedCommodityLinks++;

                    continue;
                }

                $rows[] = [
                    'commodity_id' => $commodity->id,
                    'min_percentage' => isset($part['MinPercentage']) && is_numeric($part['MinPercentage'])
                        ? (float) $part['MinPercentage']
                        : null,
                    'max_percentage' => isset($part['MaxPercentage']) && is_numeric($part['MaxPercentage'])
                        ? (float) $part['MaxPercentage']
                        : null,
                    'probability' => isset($part['Probability']) && is_numeric($part['Probability'])
                        ? (float) $part['Probability']
                        : null,
                    'quality_scale' => isset($part['QualityScale']) && is_numeric($part['QualityScale'])
                        ? (float) $part['QualityScale']
                        : null,
                    'curve_exponent' => isset($part['CurveExponent']) && is_numeric($part['CurveExponent'])
                        ? (float) $part['CurveExponent']
                        : null,
                ];
            }
        }

        $topLevelParts = $resourcePayload['Parts'] ?? [];

        if (is_array($topLevelParts)) {
            foreach ($topLevelParts as $part) {
                if (! is_array($part)) {
                    continue;
                }

                $resourceTypes = $part['ResourceTypes'] ?? [];

                if (! is_array($resourceTypes)) {
                    continue;
                }

                foreach ($resourceTypes as $resourceType) {
                    if (! is_array($resourceType)) {
                        continue;
                    }

                    $commodityUuid = $resourceType['ResourceTypeUUID'] ?? null;

                    if ($commodityUuid === null) {
                        continue;
                    }

                    $commodity = $commodityLookup->get($commodityUuid);

                    if ($commodity === null) {
                        $skippedCommodityLinks++;

                        continue;
                    }

                    $existingWeights = collect($rows)
                        ->filter(static fn (array $r): bool => $r['commodity_id'] === $commodity->id && isset($r['weight']));

                    if ($existingWeights->isNotEmpty()) {
                        continue;
                    }

                    $rows[] = [
                        'commodity_id' => $commodity->id,
                        'weight' => isset($resourceType['Weight']) && is_numeric($resourceType['Weight'])
                            ? (float) $resourceType['Weight']
                            : null,
                    ];
                }
            }
        }

        return $rows;
    }

    private function isValidResourcePayload(array $resourcePayload): bool
    {
        return ($resourcePayload['UUID'] ?? null) !== null
            && ($resourcePayload['Key'] ?? null) !== null
            && ($resourcePayload['Kind'] ?? null) !== null;
    }
}
