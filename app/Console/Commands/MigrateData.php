<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class MigrateData extends Command
{
    /**
     * # List groups
     * php artisan data:migrate --list
     *
     * # Migrate one group
     * php artisan data:migrate --group=CommLinks
     *
     * # Migrate multiple groups
     * php artisan data:migrate --group=ShipMatrix --group=Galactapedia
     *
     * # Migrate everything
     * php artisan data:migrate --all
     *
     * # Dry run (no writes)
     * php artisan data:migrate --group=ShipMatrix --dry-run
     *
     * # Truncate target tables first
     * php artisan data:migrate --group=ShipMatrix --truncate
     *
     * # Smaller batches if you hit parameter limits
     * php artisan data:migrate --group=ShipMatrix --chunk=250
     */
    protected $signature = 'data:migrate
        {--group=* : Group(s) to migrate (repeatable). Example: --group=CommLinks --group=ShipMatrix}
        {--all : Migrate all groups}
        {--list : List available groups and their tables}
        {--from= : Source connection name (defaults to config data_migration.from_connection)}
        {--to= : Destination connection name (defaults to config data_migration.to_connection)}
        {--chunk=1000 : Rows per batch insert}
        {--truncate : Truncate destination tables before inserting}
        {--force : Migrate even if destination table already has rows (may cause PK conflicts)}
        {--dry-run : Read and map rows but do not write to destination}
        {--no-progress : Disable progress bars}';

    protected $description = 'Migrate selected table groups from MariaDB to Postgres.';

    public function handle(): int
    {
        $cfg = config('data_migration');
        $groupsCfg = $cfg['groups'] ?? [];

        if (empty($groupsCfg)) {
            $this->error('No groups configured in config/data_migration.php');

            return self::FAILURE;
        }

        if ($this->option('list')) {
            $this->listGroups($groupsCfg);

            return self::SUCCESS;
        }

        $fromName = (string) ($this->option('from') ?: ($cfg['from_connection'] ?? 'legacy_mysql'));
        $toName = (string) ($this->option('to') ?: ($cfg['to_connection'] ?? 'pgsql'));

        $from = DB::connection($fromName);
        $to = DB::connection($toName);

        $selectedGroups = $this->resolveGroups(array_keys($groupsCfg));
        if (empty($selectedGroups)) {
            $this->error('No groups selected. Use --group=Name or --all. Use --list to see options.');

            return self::FAILURE;
        }

        $chunk = max(1, (int) $this->option('chunk'));
        $truncate = (bool) $this->option('truncate');
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');
        $noProgress = (bool) $this->option('no-progress');

        foreach ($selectedGroups as $group) {
            $this->info("Group: {$group}");

            $tables = $groupsCfg[$group] ?? [];
            foreach ($tables as $def) {
                $tableDef = $this->normalizeTableDef($def);

                try {
                    $this->migrateTable(
                        from: $from,
                        to: $to,
                        tableDef: $tableDef,
                        chunk: $chunk,
                        truncate: $truncate,
                        force: $force,
                        dryRun: $dryRun,
                        noProgress: $noProgress,
                    );
                } catch (Throwable $e) {
                    $this->error("Failed table {$tableDef['table']}: {$e->getMessage()}");

                    return self::FAILURE;
                }
            }
        }

        return self::SUCCESS;
    }

    private function listGroups(array $groupsCfg): void
    {
        $this->line('Available groups:');
        foreach ($groupsCfg as $group => $tables) {
            $this->line("- {$group}");
            foreach ($tables as $t) {
                $def = $this->normalizeTableDef($t);
                $this->line("  - {$def['table']}".($def['target'] !== $def['table'] ? " -> {$def['target']}" : ''));
            }
        }
    }

    private function resolveGroups(array $allGroupNames): array
    {
        if ($this->option('all')) {
            return $allGroupNames;
        }

        $requested = (array) $this->option('group');
        $requested = array_values(array_filter(array_map('trim', $requested)));

        if (empty($requested)) {
            return [];
        }

        $map = [];
        foreach ($allGroupNames as $g) {
            $map[Str::lower($g)] = $g;
        }

        $resolved = [];
        foreach ($requested as $r) {
            $key = Str::lower($r);
            if (! isset($map[$key])) {
                $this->warn("Unknown group '{$r}' (ignored). Use --list to see valid groups.");

                continue;
            }
            $resolved[] = $map[$key];
        }

        return array_values(array_unique($resolved));
    }

    private function normalizeTableDef(string|array $def): array
    {
        if (is_string($def)) {
            return [
                'table' => $def,
                'target' => $def,
                'primary_key' => 'id',
                'rename' => [],
                'drop' => [],
                'defaults' => [],
                'post_updates' => [],
            ];
        }

        return array_merge(
            [
                'target' => $def['table'] ?? null,
                'primary_key' => 'id',
                'rename' => [],
                'drop' => [],
                'defaults' => [],
                'post_updates' => [],
            ],
            $def,
        );
    }

    private function migrateTable(
        ConnectionInterface $from,
        ConnectionInterface $to,
        array $tableDef,
        int $chunk,
        bool $truncate,
        bool $force,
        bool $dryRun,
        bool $noProgress,
    ): void {
        $sourceTable = $tableDef['table'];
        $targetTable = $tableDef['target'] ?? $sourceTable;
        $pk = $tableDef['primary_key'] ?: null;

        $this->line("  Table: {$sourceTable} -> {$targetTable}");

        if (! $truncate && ! $force) {
            $hasAnyRows = $to->table($targetTable)->limit(1)->exists();
            if ($hasAnyRows) {
                $this->line('    Skipped (destination table already contains data). Use --force or --truncate.');

                return;
            }
        }

        $sourceCols = Schema::connection($from->getName())->getColumnListing($sourceTable);
        $targetCols = Schema::connection($to->getName())->getColumnListing($targetTable);

        if (empty($sourceCols)) {
            $this->warn("    Source table has no columns or does not exist: {$sourceTable}");

            return;
        }
        if (empty($targetCols)) {
            $this->warn("    Target table has no columns or does not exist: {$targetTable}");

            return;
        }

        $rename = (array) ($tableDef['rename'] ?? []);
        $drop = array_fill_keys((array) ($tableDef['drop'] ?? []), true);
        $defaults = (array) ($tableDef['defaults'] ?? []);

        $map = [];
        foreach ($sourceCols as $src) {
            if (isset($drop[$src])) {
                continue;
            }

            $dst = $rename[$src] ?? $src;
            if (in_array($dst, $targetCols, true)) {
                $map[$src] = $dst;
            }
        }

        foreach ($defaults as $dst => $_val) {
            if (! in_array($dst, $targetCols, true)) {
                $this->warn("    Default column not found on target (ignored): {$dst}");
                unset($defaults[$dst]);
            }
        }

        if (empty($map) && empty($defaults)) {
            $this->warn('    No compatible columns found to migrate (after rename/drop filtering).');

            return;
        }

        if ($truncate && ! $dryRun) {
            $this->truncateTargetTable($to, $targetTable);
        }

        $query = $from->table($sourceTable);

        $total = null;
        if (! $noProgress) {
            try {
                $total = $query->count();
            } catch (Throwable) {
                $total = null;
            }
        }

        $bar = null;
        if (! $noProgress && is_int($total)) {
            $bar = $this->output->createProgressBar($total);
            $bar->start();
        }

        $insertBatch = function ($rows) use ($to, $targetTable, $map, $defaults, $dryRun, $bar) {
            $payload = [];
            foreach ($rows as $row) {
                $out = [];

                foreach ($map as $src => $dst) {
                    $val = $row->{$src} ?? null;
                    $out[$dst] = $this->sanitizeValue($val);
                }

                foreach ($defaults as $dst => $val) {
                    $out[$dst] = $val;
                }

                $payload[] = $out;
            }

            if (! $dryRun && ! empty($payload)) {
                $to->table($targetTable)->insert($payload);
            }

            $bar?->advance(count($rows));
        };

        $useChunkById = $pk && in_array($pk, $sourceCols, true);

        if ($useChunkById) {
            $query->orderBy($pk)->chunkById($chunk, $insertBatch, $pk);
        } else {
            $orderCol = $sourceCols[0];
            $this->warn("    No usable primary key found for chunkById; using offset chunk ordered by '{$orderCol}'.");
            $query->orderBy($orderCol)->chunk($chunk, $insertBatch);
        }

        if ($bar) {
            $bar->finish();
            $this->newLine();
        }

        if (! $dryRun && ! empty($tableDef['post_updates'])) {
            $this->applyPostUpdates(
                from: $from,
                to: $to,
                tableDef: $tableDef,
                chunk: $chunk,
                noProgress: $noProgress,
            );
        }

        // Automatically sync sequences for PostgreSQL unless explicitly disabled
        $isPostgres = $to->getDriverName() === 'pgsql';
        $isPivot = in_array(
            $targetTable, [
                'comm_link_image',
                'comm_link_link',
                'shipmatrix_vehicle_loaners',
                'shipmatrix_vehicle_vehicle_focus',
                'galactapedia_article_templates',
                'galactapedia_article_tags',
                'galactapedia_article_relates',
                'galactapedia_article_categories',
            ],
            true
        );

        if ($isPostgres && ! $dryRun && ! $isPivot) {
            $this->syncPostgresSequenceBestEffort($to, $targetTable, $pk ?: 'id');
        }
    }

    private function applyPostUpdates(
        ConnectionInterface $from,
        ConnectionInterface $to,
        array $tableDef,
        int $chunk,
        bool $noProgress,
    ): void {
        $sourceTable = $tableDef['table'];
        $targetTable = $tableDef['target'] ?? $sourceTable;
        $pk = $tableDef['primary_key'] ?: 'id';

        foreach ((array) $tableDef['post_updates'] as $upd) {
            $column = (string) ($upd['column'] ?? '');
            $sourceColumn = (string) ($upd['source_column'] ?? $column);
            $refTable = (string) ($upd['ref_table'] ?? '');
            $refColumn = (string) ($upd['ref_column'] ?? 'id');

            if ($column === '' || $sourceColumn === '') {
                $this->warn('    post_updates entry missing column/source_column (skipped).');

                continue;
            }

            $this->line("    Post-update: {$targetTable}.{$column} from {$sourceTable}.{$sourceColumn}");

            $q = $from->table($sourceTable)
                ->select([$pk, $sourceColumn])
                ->whereNotNull($sourceColumn)
                ->orderBy($pk);

            $total = null;
            if (! $noProgress) {
                try {
                    $total = (clone $q)->count();
                } catch (Throwable) {
                    $total = null;
                }
            }

            $bar = null;
            if (! $noProgress && is_int($total)) {
                $bar = $this->output->createProgressBar($total);
                $bar->start();
            }

            $q->chunkById($chunk, function ($rows) use ($to, $targetTable, $pk, $column, $sourceColumn, $refTable, $refColumn, $bar) {
                $pairs = [];
                $refIds = [];

                foreach ($rows as $r) {
                    $id = $r->{$pk};
                    $val = $r->{$sourceColumn};

                    $val = $this->sanitizeValue($val);

                    $pairs[] = ['__id' => $id, '__val' => $val];
                    if ($val !== null) {
                        $refIds[] = $val;
                    }
                }

                $refSet = null;
                if ($refTable !== '' && ! empty($refIds)) {
                    $refIds = array_values(array_unique($refIds));
                    $existing = $to->table($refTable)
                        ->whereIn($refColumn, $refIds)
                        ->pluck($refColumn)
                        ->all();

                    $refSet = array_fill_keys($existing, true);
                }

                $payload = [];
                foreach ($pairs as $p) {
                    $id = $p['__id'];
                    $val = $p['__val'];

                    if ($refSet !== null && $val !== null && ! isset($refSet[$val])) {
                        $val = null;
                    }

                    $payload[] = [
                        $pk => $id,
                        $column => $val,
                    ];
                }

                if (! empty($payload)) {
                    $to->table($targetTable)->upsert($payload, [$pk], [$column]);
                }

                $bar?->advance(count($rows));
            }, $pk);

            if ($bar) {
                $bar->finish();
                $this->newLine();
            }
        }
    }

    private function truncateTargetTable(ConnectionInterface $to, string $table): void
    {
        $wrapped = $to->getQueryGrammar()->wrapTable($table);
        $to->statement("TRUNCATE TABLE {$wrapped} RESTART IDENTITY CASCADE");
    }

    private function sanitizeValue(mixed $val): mixed
    {
        if (is_string($val)) {
            if ($val === '0000-00-00' || $val === '0000-00-00 00:00:00') {
                return null;
            }
        }

        return $val;
    }

    private function syncPostgresSequenceBestEffort(ConnectionInterface $to, string $table, string $pk): void
    {
        try {
            $wrappedTable = $to->getQueryGrammar()->wrapTable($table);
            $wrappedPk = $to->getQueryGrammar()->wrap($pk);

            // Get current sequence value before syncing
            $seqName = $to->selectOne('SELECT pg_get_serial_sequence(?, ?)', [$table, $pk])?->pg_get_serial_sequence;

            if ($seqName === null) {
                $this->warn("    No sequence found for {$table}.{$pk} - skipping sync");

                return;
            }

            $beforeValue = $to->selectOne("SELECT last_value FROM {$seqName}")?->last_value;
            $maxId = $to->selectOne("SELECT MAX({$wrappedPk}) FROM {$wrappedTable}")?->max;

            $sql = "
                SELECT setval(
                    pg_get_serial_sequence(?, ?),
                    COALESCE((SELECT MAX({$wrappedPk}) FROM {$wrappedTable}), 1),
                    true
                )
            ";

            $to->select($sql, [$table, $pk]);

            $afterValue = $maxId ?? 1;

            if ($beforeValue !== $afterValue) {
                $this->info("    ✓ Synced sequence {$seqName}: {$beforeValue} → {$afterValue}");
            } else {
                $this->line("    ✓ Sequence {$seqName} already in sync ({$beforeValue})");
            }
        } catch (Throwable $e) {
            $this->error("    ✗ Sequence sync failed for {$table}.{$pk}: {$e->getMessage()}");
        }
    }
}
