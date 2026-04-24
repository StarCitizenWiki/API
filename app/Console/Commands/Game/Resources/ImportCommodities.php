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
    protected $signature = 'game:import-commodities {--path=resources/commodities.json : Relative path to the commodities JSON file on the scunpacked disk}';

    protected $description = 'Import game commodities from scunpacked data';

    public function handle(): int
    {
        $path = (string) $this->option('path');

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

                $uuid = $this->normalizeString($commodity['UUID'] ?? null);
                $key = $this->normalizeString($commodity['Key'] ?? null);

                if ($uuid === null || $key === null) {
                    $skipped++;

                    return false;
                }

                return true;
            })
            ->keyBy(fn (array $commodity): string => (string) $commodity['UUID']);

        $slugService = app(SlugService::class);
        $usedSlugs = [];
        $slugMap = [];
        $commodities->each(function (array $commodity) use ($slugService, &$usedSlugs, &$slugMap): void {
            $name = (string) ($commodity['Name'] ?? '');
            $baseSlug = Str::slug($name);

            if ($baseSlug === '') {
                $baseSlug = Str::slug((string) ($commodity['Key'] ?? 'commodity'));
            }

            $slug = $slugService->generateUniqueSlugForBatch($baseSlug, $usedSlugs, Commodity::class);
            $slugMap[$commodity['UUID']] = $slug;
        });

        $commodities = $commodities->map(function (array $commodity) use ($now, $slugMap): array {
            $cargoContainers = $commodity['CargoContainers'] ?? [];

            $boxSizes = is_array($cargoContainers)
                ? collect($cargoContainers)->pluck('Size')->values()->all()
                : [];

            return [
                'uuid' => (string) $commodity['UUID'],
                'key' => (string) $commodity['Key'],
                'name' => (string) ($commodity['Name'] ?? ''),
                'slug' => $slugMap[$commodity['UUID']] ?? Str::slug((string) ($commodity['Name'] ?? '')),
                'description' => (string) ($commodity['Description'] ?? ''),
                'refined_version_uuid' => $this->normalizeString($commodity['RefinedVersionUUID'] ?? null),
                'refined_version_name' => $this->normalizeString($commodity['RefinedVersionName'] ?? null),
                'validate_default_cargo_box' => (bool) ($commodity['ValidateDefaultCargoBox'] ?? false),
                'has_default_cargo_containers' => (bool) ($commodity['HasDefaultCargoContainers'] ?? false),
                'tier' => $this->normalizeString($commodity['Tier'] ?? null),
                'box_sizes_scu' => json_encode($boxSizes, JSON_THROW_ON_ERROR),
                'quality_distribution_uuid' => $this->normalizeString($commodity['QualityDistributionUUID'] ?? null),
                'quality_location_override_uuid' => $this->normalizeString($commodity['QualityLocationOverrideUUID'] ?? null),
                'instability' => $commodity['Instability'] ?? null,
                'resistance' => $commodity['Resistance'] ?? null,
                'density_g_per_cc' => $commodity['DensityGPerCc'] ?? null,
                'data' => json_encode($commodity, JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        });

        if ($commodities->isEmpty()) {
            $this->warn('No valid commodity records found to import.');

            return self::SUCCESS;
        }

        $uuids = $commodities->keys()->all();

        $existing = Commodity::query()
            ->whereIn('uuid', $uuids)
            ->pluck('uuid')
            ->all();

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
                'data',
                'updated_at',
            ]
        );

        $created = count(array_diff($uuids, $existing));
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

    private function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
