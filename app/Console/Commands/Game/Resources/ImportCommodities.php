<?php

declare(strict_types=1);

namespace App\Console\Commands\Game\Resources;

use App\Models\Game\Commodity\Commodity;
use App\Services\Game\SlugService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;

class ImportCommodities extends Command
{
    protected $signature = 'game:import-commodities {--path=resources/commodities.json : Relative path to the commodities JSON file on the scunpacked disk} {--disk=scunpacked : Storage disk to read from}';

    protected $description = 'Import game commodities from scunpacked data';

    public function handle(): int
    {
        $path = (string) $this->option('path');

        $disk = Storage::disk($this->option('disk'));

        if ($disk->missing($path)) {
            $this->error(sprintf('%s not found in scunpacked storage.', $path));

            return self::FAILURE;
        }

        try {
            $contents = $disk->get($path);
            $payload = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->error(sprintf('Failed to decode %s: %s', $path, $exception->getMessage()));

            return self::FAILURE;
        }

        if (! is_array($payload)) {
            $this->error(sprintf('%s must contain an array of commodities.', $path));

            return self::FAILURE;
        }

        $now = now();
        $skipped = 0;

        $commodities = collect($payload)
            ->filter(function (mixed $commodity) use (&$skipped): bool {
                if (! is_array($commodity)) {
                    $skipped++;

                    return false;
                }

                $uuid = $commodity['UUID'] ?? null;
                $key = $commodity['Key'] ?? null;

                if ($uuid === null || $key === null) {
                    $skipped++;

                    return false;
                }

                return true;
            })
            ->keyBy(fn (array $commodity): string => (string) $commodity['UUID']);

        $payloadUuids = $commodities->keys()->values()->all();

        $existingSlugs = Commodity::query()
            ->whereIn('uuid', $payloadUuids)
            ->pluck('slug', 'uuid')
            ->all();
        $existingUuids = array_keys($existingSlugs);

        $slugService = app(SlugService::class);

        $usedSlugs = array_values(array_filter(
            $existingSlugs,
            static fn ($slug): bool => $slug !== null && $slug !== '',
        ));

        $slugMap = [];

        $commodities->each(function (array $commodity) use ($payloadUuids, $existingSlugs, $slugService, &$usedSlugs, &$slugMap): void {
            $uuid = (string) $commodity['UUID'];

            $preserved = $existingSlugs[$uuid] ?? null;
            if ($preserved !== null && $preserved !== '') {
                $slugMap[$uuid] = $preserved;

                return;
            }

            $name = strip_tags((string) ($commodity['Name'] ?? ''));
            $baseSlug = Str::slug($name);

            if ($baseSlug === '') {
                $baseSlug = Str::slug((string) ($commodity['Key'] ?? 'commodity'));
            }

            $slugMap[$uuid] = $slugService->generateUniqueSlugForBatch(
                $baseSlug,
                $usedSlugs,
                Commodity::class,
                ignoredValuesByColumn: ['uuid' => $payloadUuids],
            );
        });

        $commodities = $commodities->map(function (array $commodity) use ($now, $slugMap): array {
            $cargoContainers = $commodity['CargoContainers'] ?? [];

            $boxSizes = is_array($cargoContainers)
                ? collect($cargoContainers)->pluck('Size')->values()->all()
                : [];

            return [
                'uuid' => (string) $commodity['UUID'],
                'key' => (string) $commodity['Key'],
                'name' => strip_tags((string) ($commodity['Name'] ?? '')),
                'slug' => $slugMap[$commodity['UUID']] ?? Str::slug(strip_tags((string) ($commodity['Name'] ?? ''))),
                'description' => (string) ($commodity['Description'] ?? ''),
                'refined_version_uuid' => $commodity['RefinedVersionUUID'] ?? null,
                'refined_version_name' => $commodity['RefinedVersionName'] ?? null,
                'validate_default_cargo_box' => (bool) ($commodity['ValidateDefaultCargoBox'] ?? false),
                'has_default_cargo_containers' => (bool) ($commodity['HasDefaultCargoContainers'] ?? false),
                'tier' => $commodity['Tier'] ?? null,
                'box_sizes_scu' => json_encode($boxSizes, JSON_THROW_ON_ERROR),
                'quality_distribution_uuid' => $commodity['QualityDistributionUUID'] ?? null,
                'quality_location_override_uuid' => $commodity['QualityLocationOverrideUUID'] ?? null,
                'instability' => $commodity['Instability'] ?? null,
                'resistance' => $commodity['Resistance'] ?? null,
                'density_g_per_cc' => $commodity['DensityGPerCc'] ?? null,
                'volatility' => $commodity['Volatility'] ?? null,
                'volatility_health_decay_per_second' => $commodity['VolatilityHealthDecayPerSecond'] ?? null,
                'data' => json_encode($commodity, JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        });

        if ($commodities->isEmpty()) {
            $this->warn('No valid commodity records found to import.');

            return self::SUCCESS;
        }

        Commodity::query()->upsert(
            $commodities->values()->all(),
            ['uuid'],
            [
                'key',
                'name',
                'slug',
                'description',
                'refined_version_uuid',
                'refined_version_name',
                'validate_default_cargo_box',
                'has_default_cargo_containers',
                'tier',
                'box_sizes_scu',
                'quality_distribution_uuid',
                'quality_location_override_uuid',
                'instability',
                'resistance',
                'density_g_per_cc',
                'volatility',
                'volatility_health_decay_per_second',
                'data',
                'updated_at',
            ]
        );

        $created = count(array_diff($payloadUuids, $existingUuids));
        $updated = $commodities->count() - $created;

        $this->info(sprintf(
            'Imported %d commodities (%d new, %d updated). Skipped %d invalid.',
            $commodities->count(),
            $created,
            $updated,
            $skipped
        ));

        return self::SUCCESS;
    }
}
