<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Models\Game\GameVersion;
use App\Models\Game\VersionDiff;
use App\Support\Game\DeepDiff;
use App\Support\Game\EntityTypeConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComputeVersionDiff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:compute-version-diff
        {version? : Version code to compute diff for (defaults to latest)}
        {--from= : Explicit previous version code (overrides auto-detection)}
        {--type= : Only compute diff for a specific entity type (item, vehicle, blueprint, mission)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compute diff between a game version and its predecessor';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $version = $this->resolveVersion();

        if ($version === null) {
            return self::FAILURE;
        }

        $previousVersion = $this->resolvePreviousVersion($version);

        if ($previousVersion === null) {
            $this->error(sprintf('No previous version found for "%s".', $version->code));

            return self::FAILURE;
        }

        if ($this->diffAlreadyExists($previousVersion, $version)) {
            if (! $this->confirm(sprintf('Diff for %s -> %s already exists. Recompute?', $previousVersion->code, $version->code), false)) {
                $this->info('Aborted.');

                return self::SUCCESS;
            }

            $this->deleteExistingDiff($previousVersion, $version);
        }

        $this->info(sprintf('Computing diff: %s -> %s', $previousVersion->code, $version->code));

        $types = $this->resolveEntityTypes();
        $total = 0;

        foreach ($types as $key => $config) {
            $count = $this->computeEntityDiff(
                $config['data_model'],
                $config['foreign_key'],
                $config['model'],
                $config['columns'],
                $previousVersion->id,
                $version->id,
            );
            $this->info(sprintf('  %s: %d changes', $config['label'], $count));
            $total += $count;
        }

        $this->info(sprintf('Done. %d total changes.', $total));

        return self::SUCCESS;
    }

    /**
     * @return array<string, array{model: class-string, data_model: class-string, foreign_key: string, columns: string[], label: string}>
     */
    private function resolveEntityTypes(): array
    {
        $typeFilter = $this->option('type');

        if ($typeFilter !== null) {
            $config = EntityTypeConfig::get($typeFilter);

            if ($config === null) {
                $this->error(sprintf('Unknown entity type "%s". Available: %s', $typeFilter, implode(', ', array_keys(EntityTypeConfig::all()))));

                return [];
            }

            return [$typeFilter => $config];
        }

        return EntityTypeConfig::all();
    }

    private function resolveVersion(): ?GameVersion
    {
        $code = $this->argument('version');

        if ($code !== null) {
            $version = GameVersion::findByCode((string) $code);

            if ($version === null) {
                $this->error(sprintf('Version "%s" not found.', $code));

                return null;
            }

            return $version;
        }

        $version = GameVersion::orderByDesc('released_at')->first();

        if ($version === null) {
            $this->error('No game versions found.');

            return null;
        }

        return $version;
    }

    private function resolvePreviousVersion(GameVersion $version): ?GameVersion
    {
        $fromCode = $this->option('from');

        if ($fromCode !== null) {
            return GameVersion::findByCode((string) $fromCode);
        }

        return $version->findPreviousVersion();
    }

    private function diffAlreadyExists(GameVersion $from, GameVersion $to): bool
    {
        return VersionDiff::query()
            ->forVersionPair($from->id, $to->id)
            ->exists();
    }

    private function deleteExistingDiff(GameVersion $from, GameVersion $to): void
    {
        VersionDiff::query()
            ->forVersionPair($from->id, $to->id)
            ->delete();
    }

    /**
     * @param  class-string  $dataModel
     * @param  class-string  $entityModel
     * @param  array<int, string>  $columns
     */
    private function computeEntityDiff(string $dataModel, string $foreignKey, string $entityModel, array $columns, int $fromVersionId, int $toVersionId): int
    {
        $entityIds = $dataModel::where('game_version_id', $fromVersionId)
            ->orWhere('game_version_id', $toVersionId)
            ->pluck($foreignKey)
            ->unique()
            ->values();

        $count = 0;
        $batch = [];

        foreach ($entityIds->chunk(1000) as $chunk) {
            $oldEntities = $dataModel::where('game_version_id', $fromVersionId)
                ->whereIn($foreignKey, $chunk)
                ->get()
                ->keyBy($foreignKey);

            $newEntities = $dataModel::where('game_version_id', $toVersionId)
                ->whereIn($foreignKey, $chunk)
                ->get()
                ->keyBy($foreignKey);

            foreach ($chunk as $entityId) {
                $old = $oldEntities->get($entityId);
                $new = $newEntities->get($entityId);

                if ($old === null && $new !== null) {
                    $batch[] = $this->buildRow($fromVersionId, $toVersionId, $entityModel, $entityId, 'added', null, null);
                    $count++;

                    continue;
                }

                if ($old !== null && $new === null) {
                    $batch[] = $this->buildRow($fromVersionId, $toVersionId, $entityModel, $entityId, 'removed', null, null);
                    $count++;

                    continue;
                }

                $columnChanges = DeepDiff::diffColumns($old->toArray(), $new->toArray(), $columns);
                $dataChanges = DeepDiff::diff($old->data, $new->data);

                $dataChanges = array_filter(
                    $dataChanges,
                    fn (string $path) => ! in_array(Str::of($path)->afterLast('.')->toString(), ['Description', 'DescriptionText']),
                    ARRAY_FILTER_USE_KEY
                );

                if ($columnChanges === [] && $dataChanges === []) {
                    continue;
                }

                $batch[] = $this->buildRow($fromVersionId, $toVersionId, $entityModel, $entityId, 'modified', $columnChanges, $dataChanges);
                $count++;

                if (count($batch) >= 500) {
                    $this->upsertBatch($batch);
                    $batch = [];
                }
            }
        }

        if ($batch !== []) {
            $this->upsertBatch($batch);
        }

        return $count;
    }

    /**
     * @param  array<string, mixed>|null  $columnChanges
     * @param  array<string, mixed>|null  $dataChanges
     * @return array<string, mixed>
     */
    private function buildRow(int $fromVersionId, int $toVersionId, string $entityType, int $entityId, string $changeType, ?array $columnChanges, ?array $dataChanges): array
    {
        return [
            'from_version_id' => $fromVersionId,
            'to_version_id' => $toVersionId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'change_type' => $changeType,
            'column_changes' => $columnChanges === [] ? null : json_encode($columnChanges),
            'data_changes' => $dataChanges === [] ? null : json_encode($dataChanges),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $batch
     */
    private function upsertBatch(array $batch): void
    {
        DB::table('game_version_diffs')->upsert(
            $batch,
            ['from_version_id', 'to_version_id', 'entity_type', 'entity_id'],
            ['change_type', 'column_changes', 'data_changes', 'updated_at'],
        );
    }
}
