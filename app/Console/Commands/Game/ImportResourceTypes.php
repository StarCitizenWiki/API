<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Models\Game\ResourceType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use JsonException;

class ImportResourceTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:import-resource-types {--path=resource-types.json : Relative path to the resource types JSON file on the scunpacked disk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import game resource types from scunpacked data';

    /**
     * Execute the console command.
     */
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
            $this->error(sprintf('%s must contain an array of resource types.', $path));

            return self::FAILURE;
        }

        $now = now();
        $skipped = 0;

        $resourceTypes = collect($payload)
            ->filter(function (mixed $resourceType) use (&$skipped): bool {
                if (! is_array($resourceType)) {
                    $skipped++;

                    return false;
                }

                $uuid = $this->normalizeString($resourceType['uuid'] ?? null);
                $key = $this->normalizeString($resourceType['key'] ?? null);

                if ($uuid === null || $key === null) {
                    $skipped++;

                    return false;
                }

                return true;
            })
            ->keyBy(fn (array $resourceType): string => (string) $resourceType['uuid'])
            ->map(function (array $resourceType) use ($now): array {
                return [
                    'uuid' => (string) $resourceType['uuid'],
                    'key' => (string) $resourceType['key'],
                    'name' => (string) ($resourceType['name'] ?? ''),
                    'description' => (string) ($resourceType['description'] ?? ''),
                    'refined_version_uuid' => $this->normalizeString($resourceType['refined_version_uuid'] ?? null),
                    'validate_default_cargo_box' => (bool) ($resourceType['validate_default_cargo_box'] ?? false),
                    'has_default_cargo_containers' => (bool) ($resourceType['has_default_cargo_containers'] ?? false),
                    'box_sizes_scu' => json_encode(
                        is_array($resourceType['box_sizes_scu'] ?? null) ? $resourceType['box_sizes_scu'] : [],
                        JSON_THROW_ON_ERROR
                    ),
                    'data' => json_encode($resourceType, JSON_THROW_ON_ERROR),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            });

        if ($resourceTypes->isEmpty()) {
            $this->warn('No valid resource type records found to import.');

            return self::SUCCESS;
        }

        $uuids = $resourceTypes->keys()->all();

        $existing = ResourceType::query()
            ->whereIn('uuid', $uuids)
            ->pluck('uuid')
            ->all();

        ResourceType::query()->upsert(
            $resourceTypes->values()->all(),
            ['uuid'],
            [
                'key',
                'name',
                'description',
                'refined_version_uuid',
                'validate_default_cargo_box',
                'has_default_cargo_containers',
                'box_sizes_scu',
                'data',
                'updated_at',
            ]
        );

        $created = count(array_diff($uuids, $existing));
        $updated = $resourceTypes->count() - $created;

        $this->info(sprintf(
            'Imported %d resource types (%d new, %d updated). Skipped %d invalid.',
            $resourceTypes->count(),
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
